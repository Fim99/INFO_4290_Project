<?php
include 'api.php';
include 'bootstrap.html';

// Function to build the API URL for fetching food details
function buildApiUrl($fdcId)
{
    return "https://api.nal.usda.gov/fdc/v1/food/$fdcId?";
}

// Function to fetch and decode API response
function fetchApiData($url)
{
    $response = fetchDataFromAPI($url);
    return $response ? json_decode($response, true) : null;
}

// Function to render food details
function displayFoodDetails($data)
{
    echo "<h1>" . htmlspecialchars($data['description'] ?? '---') . "</h1>";
    echo "<p>FDC ID: " . htmlspecialchars($data['fdcId'] ?? '---') . "</p>";
    echo "<p>Data Type: " . htmlspecialchars($data['dataType'] ?? '---') . "</p>";

    // Display nutrients in a table
    echo "<h2>Food Nutrients</h2>";
    echo "<div class='col-md-3'>";
    echo "<table class='table'>";
    echo "<tr><th>Nutrient Name</th><th>Amount</th><th>Unit</th></tr>";
    foreach ($data['foodNutrients'] as $nutrient)
    {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($nutrient['nutrient']['name'] ?? '---') . "</td>";
        echo "<td>" . htmlspecialchars($nutrient['amount'] ?? '---') . "</td>";
        echo "<td>" . htmlspecialchars($nutrient['nutrient']['unitName'] ?? '---') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------
if (!isset($_GET['fdcId']))
{
    echo "No food item specified.";
    return;
}

// Get the fdcId from the URL parameter
$fdcId = urlencode($_GET['fdcId']);

// Construct the URL for the API request
$url = buildApiUrl($fdcId);

// Call the fetchDataFromAPI function to retrieve data from the API
$data = fetchApiData($url);

// Check if the response is null, indicating an error
if ($data === null)
{
    echo "An error occurred while fetching data from the API.";
    return;
}

// Check if the response contains the food item details
if (!isset($data['description']))
{
    echo "No details found for this food item.";
    return;
}

// Check if foodNutrients are present and not empty
if (!isset($data['foodNutrients']) || !is_array($data['foodNutrients']) || count($data['foodNutrients']) === 0)
{
    echo "<p>No nutrient information available.</p>";
    return;
}

// Display food details
displayFoodDetails($data);

?>