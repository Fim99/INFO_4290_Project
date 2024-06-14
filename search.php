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
            // Display the results in a table
            echo "<table class='table'>";

            // Determine which headers to display based on dataType
            $headers = [];
            switch ($_GET['dataType'])
            {
                case 'Branded':
                    $headers = ['Description', 'FDC ID', 'Food Category', 'Brand Owner', 'Brand', 'Market Country'];
                    break;
                case 'Foundation':
                    $headers = ['Description', 'FDC ID', 'Most Recent Acquisition', 'SR/ Foundation Category'];
                    break;
                case 'Survey (FNDDS)':
                    $headers = ['Description', 'FDC ID', 'Additional Food Description', 'WWEIA Food Category'];
                    break;
                case 'SR Legacy':
                    $headers = ['Description', 'FDC ID', 'SR Food Category'];
                    break;
                default:
                    $headers = ['Description', 'FDC ID']; // Default headers
                    break;
            }

            // Output the table headers
            echo "<tr>";
            foreach ($headers as $header)
            {
                echo "<th>$header</th>";
            }
            echo "</tr>";

            // Iterate over each food item
            foreach ($data['foods'] as $food)
            {
                echo "<tr>";
                // Make the description clickable
                echo "<td><a href='food.php?fdcId=" . urlencode($food['fdcId']) . "'>" . (empty($food['description']) ? "---" : htmlspecialchars($food['description'])) . "</a></td>";
                echo "<td>" . (empty($food['fdcId']) ? "---" : htmlspecialchars($food['fdcId'])) . "</td>";

                // Additional columns based on dataType
                switch ($_GET['dataType'])
                {
                    case 'Branded':
                        echo "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
                        echo "<td>" . (empty($food['brandOwner']) ? "---" : htmlspecialchars($food['brandOwner'])) . "</td>";
                        echo "<td>" . (empty($food['brandName']) ? "---" : htmlspecialchars($food['brandName'])) . "</td>";
                        echo "<td>" . (empty($food['marketCountry']) ? "---" : htmlspecialchars($food['marketCountry'])) . "</td>";
                        break;
                    case 'Foundation':
                        echo "<td>" . (empty($food['mostRecentAcquisitionDate']) ? "---" : htmlspecialchars($food['mostRecentAcquisitionDate'])) . "</td>";
                        echo "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
                        break;
                    case 'Survey (FNDDS)':
                        echo "<td>" . (empty($food['additionalDescriptions']) ? "---" : htmlspecialchars($food['additionalDescriptions'])) . "</td>";
                        echo "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
                        break;
                    case 'SR Legacy':
                        echo "<td>" . (empty($food['foodCategory']) ? "---" : htmlspecialchars($food['foodCategory'])) . "</td>";
                        break;
                    default:
                        // No additional columns needed for default case
                        break;
                }

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