<?php
// API key for accessing the API
$apiKey = 'VNscQ3Q7BUmM2QIwcGWTVAfaPXH9OrrCq9EadYCA';

// Function to fetch data from the API
function fetchDataFromAPI($url, $fetchFunction = null)
{
    // Access the global apiKey variable
    global $apiKey;

    // Append the API key to the URL to authenticate the request
    $url .= "&api_key=$apiKey";

    // Use the provided fetch function or default to file_get_contents
    $fetchFunction = $fetchFunction ?: 'file_get_contents';

    // Fetch data from the API using the fetch function
    $response = @$fetchFunction($url);

    // Check if the response is false, which indicates an error
    if ($response === false)
    {
        // Return null to indicate an error
        return null;
    }

    // Return the response from the API
    return $response;
}
?>
