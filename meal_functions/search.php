<?php
require_once 'api.php';
include_once 'account_functions/db_connection.php';

// Function to get additional parameters from the URL
function getAdditionalParams($params)
{
    $additionalParams = array();
    foreach ($params as $param)
    {
        if (isset($_GET[$param]))
        {
            $additionalParams[$param] = urlencode($_GET[$param]);
        }
    }
    return $additionalParams;
}

// Function to build the URL with query and additional parameters
function buildApiUrlSearch($query, $additionalParams)
{
    $baseUrl = "https://api.nal.usda.gov/fdc/v1/foods/search?query=$query";
    foreach ($additionalParams as $key => $value)
    {
        if (!empty($value))
        {
            $baseUrl .= "&$key=$value";
        }
    }
    return $baseUrl;
}

// Function to fetch and decode API response
function fetchApiDataSearch($url)
{
    $response = fetchDataFromAPI($url);
    return $response ? json_decode($response, true) : null;
}

// Function to get table headers based on dataType
function getTableHeaders($dataType)
{
    switch ($dataType)
    {
        case 'Branded':
            return ['Description', 'FDC ID', 'Food Category', 'Brand Owner', 'Brand', 'Market Country', '<div class="text-center">Add To Meal</div>'];
        case 'Foundation':
            return ['Description', 'FDC ID', 'Most Recent Acquisition', 'SR/ Foundation Category', '<div class="text-center">Add To Meal</div>'];
        case 'Survey (FNDDS)':
            return ['Description', 'FDC ID', 'Additional Food Description', 'WWEIA Food Category', '<div class="text-center">Add To Meal</div>'];
        case 'SR Legacy':
            return ['Description', 'FDC ID', 'SR Food Category', '<div class="text-center">Add To Meal</div>'];
        default:
            return ['Description', 'FDC ID', '<div class="text-center">Add To Meal</div>']; // Default headers
    }
}

// Function to render table rows based on dataType
function displayTableRow($food, $dataType)
{
    // Concatenate HTML to variable to be returned
    $html = "<tr>";
    $html .= "<td><a href='meal_functions/food.php?fdcId=" . urlencode($food['fdcId']) . "'>" . (empty($food['description']) ? "---" : htmlspecialchars($food['description'])) . "</a></td>";
    $html .= "<td>" . (empty($food['fdcId']) ? "---" : htmlspecialchars($food['fdcId'])) . "</td>";

    switch ($dataType)
    {
        case 'Branded':
            $html .= "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
            $html .= "<td>" . (empty($food['brandOwner']) ? "---" : htmlspecialchars($food['brandOwner'])) . "</td>";
            $html .= "<td>" . (empty($food['brandName']) ? "---" : htmlspecialchars($food['brandName'])) . "</td>";
            $html .= "<td>" . (empty($food['marketCountry']) ? "---" : htmlspecialchars($food['marketCountry'])) . "</td>";
            break;
        case 'Foundation':
            $html .= "<td>" . (empty($food['mostRecentAcquisitionDate']) ? "---" : htmlspecialchars($food['mostRecentAcquisitionDate'])) . "</td>";
            $html .= "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
            break;
        case 'Survey (FNDDS)':
            $html .= "<td>" . (empty($food['additionalDescriptions']) ? "---" : htmlspecialchars($food['additionalDescriptions'])) . "</td>";
            $html .= "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
            break;
        case 'SR Legacy':
            $html .= "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
            break;
    }

    // Add the "Add to Meal" form in the last column
    $html .= "<td class='text-center'><form method='post'>";
    $html .= "<input type='hidden' name='fdcId' value='" . htmlspecialchars($food['fdcId'] ?? '---') . "'>";
    $html .= "<button type='submit' name='addToMeal' class='btn btn-primary btn-sm center'>+</button>";
    $html .= "</form></td>";

    $html .= "</tr>";
    return $html;
}

// Function to handle adding FDC ID to the current meal
function addFdcIdToMealSearch($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fdcId']) && isset($_POST['addToMeal']))
    {
        // Check if user is logged in
        if (!isset($_SESSION['id']))
        {
            $_SESSION['error_message'] = "You must be logged in to add food to a meal.";
            return;
        }

        $fdcId = $conn->real_escape_string($_POST['fdcId']);
        $currentMealId = $_SESSION['current_meal_id'] ?? null;

        if (!isset($currentMealId))
        {
            $_SESSION['error_message'] = "No current meal selected.";
            return;
        }

        // Fetch the current food_fdcid from the database
        $sql = "SELECT food_fdcid FROM meals WHERE id = $currentMealId";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $currentFoodFdcid = json_decode($row['food_fdcid'], true);

            // Add the new FDC ID to the existing array
            $currentFoodFdcid[] = $fdcId;

            // Update the database with the new array
            $newFoodFdcidJson = json_encode($currentFoodFdcid);
            $sqlUpdate = "UPDATE meals SET food_fdcid = '$newFoodFdcidJson' WHERE id = $currentMealId";

            if ($conn->query($sqlUpdate) === TRUE)
            {
                $_SESSION['success_message'] = "Food added to the current meal.";
            }
            else
            {
                $_SESSION['error_message'] = "Error updating meal: " . $conn->error;
            }
        }
        else
        {
            $_SESSION['error_message'] = "Current meal not found.";
        }
    }
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------
if (!isset($_GET['query']))
{
    echo "No query specified.";
    return;
}

// Encode the query parameter to ensure safe inclusion in the URL
$query = urlencode($_GET['query']);

// Get additional parameters from the URL
$additionalParams = getAdditionalParams(['dataType', 'pageSize', 'pageNumber', 'sortBy', 'sortOrder', 'brandOwner']);

// Check if dataType is selected
if (empty($additionalParams['dataType']))
{
    echo "No data type specified.";
    return;
}

// Build the API URL
$url = buildApiUrlSearch($query, $additionalParams);

// Fetch data from API
$data = fetchApiDataSearch($url);

// Check if data is not fetched
if ($data === null)
{
    echo "An error occurred while fetching data from the API.";
    return;
}

// Check if the response contains any food items
if (empty($data['foods']))
{
    echo "No results found.";
    return;
}

// Handle adding FDC ID to the meal
addFdcIdToMealSearch($conn);

// Display success message if set
if (isset($_SESSION['success_message']))
{
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

// Display error message if set
if (isset($_SESSION['error_message']))
{
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}

// Display the results in a table
echo "<table class='table'>";

// Determine and output table headers
$headers = getTableHeaders($_GET['dataType']);
echo "<tr>";
foreach ($headers as $header)
{
    echo "<th>$header</th>";
}
echo "</tr>";

// Iterate over each food item and display table rows
foreach ($data['foods'] as $food)
{
    echo displayTableRow($food, $_GET['dataType']);
}

echo "</table>";
?>
