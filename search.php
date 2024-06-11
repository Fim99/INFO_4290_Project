<?php
include 'api.php';

if (isset($_GET['query']))
{
    // Encode the query parameter to ensure safe inclusion in the URL
    $query = urlencode($_GET['query']);

    // Construct the base URL for the API request
    $url = "https://api.nal.usda.gov/fdc/v1/foods/search?query=$query";

    // Check for additional parameters and append them to the URL if provided
    $additionalParams = array
    (
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
            // Display the results in a table
            echo "<table class='table'>
                    <tr>
                        <th>Description</th>
                        <th>FDC ID</th>
                        <th>Food Category</th>
                        <th>Brand Owner</th>
                        <th>Brand</th>
                    </tr>";
            // Iterate over each food item in the response
            foreach ($data['foods'] as $food)
            {
                echo "<tr>";
                // Make the description clickable
                echo "<td><a href='product.php?fdcId=" . urlencode($food['fdcId']) . "'>" . 
                (empty($food['description']) ? "N/A" : htmlspecialchars($food['description'])) . "</a></td>";
                echo "<td>" . (empty($food['fdcId']) ? "N/A" : htmlspecialchars($food['fdcId'])) . "</td>";
                echo "<td>" . (empty($food['foodCategory']) ? "N/A" : htmlspecialchars($food['foodCategory'])) . "</td>";
                echo "<td>" . (empty($food['brandOwner']) ? "N/A" : htmlspecialchars($food['brandOwner'])) . "</td>";
                echo "<td>" . (empty($food['brandName']) ? "N/A" : htmlspecialchars($food['brandName'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        else
        {
            echo "No results found.";
        }
    }
}
else
{
    echo "No query specified.";
}
?>