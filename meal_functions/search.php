<?php
include 'api.php';

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
function buildApiUrl($query, $additionalParams)
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
function fetchApiData($url)
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
            return ['Description', 'FDC ID', 'Food Category', 'Brand Owner', 'Brand', 'Market Country'];
        case 'Foundation':
            return ['Description', 'FDC ID', 'Most Recent Acquisition', 'SR/ Foundation Category'];
        case 'Survey (FNDDS)':
            return ['Description', 'FDC ID', 'Additional Food Description', 'WWEIA Food Category'];
        case 'SR Legacy':
            return ['Description', 'FDC ID', 'SR Food Category'];
        default:
            return ['Description', 'FDC ID']; // Default headers
    }
}

// Function to render table rows based on dataType
function displayTableRow($food, $dataType)
{
    // Concatenate  HTML to variable to be returned
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

    $html .= "</tr>";
    return $html;
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
$url = buildApiUrl($query, $additionalParams);

// Fetch data from API
$data = fetchApiData($url);

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