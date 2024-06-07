<?php
// API key for accessing the API
$apiKey = 'VNscQ3Q7BUmM2QIwcGWTVAfaPXH9OrrCq9EadYCA';

// Function to fetch data from the API
function fetchDataFromAPI($url)
{
    // Access the global apiKey variable
    global $apiKey;

    // Append the API key to the URL to authenticate the request
    $url .= "&api_key=$apiKey";

    // Fetch data from the API using file_get_contents
    $response = @file_get_contents($url);

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