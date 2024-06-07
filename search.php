<?php
include 'api.php';

if (isset($_GET['query']))
{
    // Encode the query parameter to ensure safe inclusion in the URL
    $query = urlencode($_GET['query']);

    // Construct the base URL for the API request
    $url = "https://api.nal.usda.gov/fdc/v1/foods/search?query=$query";

    // Check for additional parameters and append them to the URL if provided
    $additionalParams = array(
        'dataType' => isset($_GET['dataType']) ? urlencode($_GET['dataType']) : '',
        'pageSize' => isset($_GET['pageSize']) ? urlencode($_GET['pageSize']) : '',
        'pageNumber' => isset($_GET['pageNumber']) ? urlencode($_GET['pageNumber']) : '',
        'sortBy' => isset($_GET['sortBy']) ? urlencode($_GET['sortBy']) : '',
        'sortOrder' => isset($_GET['sortOrder']) ? urlencode($_GET['sortOrder']) : '',
        'brandOwner' => isset($_GET['brandOwner']) ? urlencode($_GET['brandOwner']) : ''
    );

    // Append additional parameters to the URL
    foreach ($additionalParams as $key => $value)
    {
        if (!empty($value))
        {
            $url .= "&$key=$value";
        }
    }

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

        // Check if the response contains any food items
        if (isset($data['foods']) && !empty($data['foods']))
        {
            // Iterate over each food item in the response
            foreach ($data['foods'] as $food)
            {
                echo "<div>" . htmlspecialchars($food['description']) . "</div>";
            }
        }
        else
        {
            echo "No results found.";
        }
    }
}
?>
