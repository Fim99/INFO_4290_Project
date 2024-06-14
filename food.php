<?php
include 'api.php';
include 'bootstrap.html';

if (isset($_GET['fdcId']))
{
    // Get the fdcId from the URL parameter
    $fdcId = urlencode($_GET['fdcId']);

    // Construct the URL for the API request
    $url = "https://api.nal.usda.gov/fdc/v1/food/$fdcId?";

    // Call the fetchDataFromAPI function to retrieve data from the API
    $response = fetchDataFromAPI($url);

    // Check if the response is null, indicating an error
    if ($response === null)
    {
        echo "An error occurred while fetching data from the API.";
    }
    else
    {
        // Decode the JSON response into a PHP associative array
        $data = json_decode($response, true);

        // Check if the response contains the food item details
        if (isset($data))
        {
            echo "<h1>" . htmlspecialchars($data['description'] ?? 'N/A') . "</h1>";
            // Display additional details about the food item
            echo "<p>FDC ID: " . htmlspecialchars($data['fdcId'] ?? 'N/A') . "</p>";
            echo "<p>Data Type: " . htmlspecialchars($data['dataType'] ?? 'N/A') . "</p>";

            // Display nutrients in a table
            if (isset($data['foodNutrients']) && is_array($data['foodNutrients']) && count($data['foodNutrients']) > 0)
            {
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
            else
            {
                echo "<p>No nutrient information available.</p>";
            }
        }
        else
        {
            echo "No details found for this food item.";
        }
    }
}
else
{
    echo "No food item specified.";
}
?>
