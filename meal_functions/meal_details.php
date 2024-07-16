<?php

include '../nav.php';
include '../account_functions/check_loggin.php';
include 'api.php';

// Database connection details
$sql_servername = "localhost";
$sql_username = "root";
$sql_password = "";
$sql_dbname = "nutritional_tracker";

// Create database connection
$conn = new mysqli($sql_servername, $sql_username, $sql_password, $sql_dbname);
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}

// Function to build API URL
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

// Function to fetch food details using API
function fetchFoodDetails($fdcId)
{
    $url = buildApiUrl($fdcId);
    $data = fetchApiData($url);
    return $data;
}

// Function to sum up nutrient values across all foods in a meal
function sumNutrients($foods)
{
    $totalNutrients = [];

    foreach ($foods as $food)
    {
        foreach ($food['foodNutrients'] as $nutrient)
        {
            $nutrientId = $nutrient['nutrient']['id'];
            $amount = isset($nutrient['amount']) ? $nutrient['amount'] : 0;

            if (!isset($totalNutrients[$nutrientId]))
            {
                $totalNutrients[$nutrientId] = [
                    'name' => $nutrient['nutrient']['name'],
                    'amount' => 0,
                    'unitName' => $nutrient['nutrient']['unitName']
                ];
            }

            // Add the amount to the existing total
            $totalNutrients[$nutrientId]['amount'] += $amount;
        }
    }

    return $totalNutrients;
}

// Function to retrieve meal details from database
function getMealDetails($conn, $mealId)
{
    $sql = "SELECT name, created_at, food_fdcId FROM meals WHERE id = $mealId";
    $result = $conn->query($sql);

    if (!$result || $result->num_rows === 0)
    {
        return null; // Return null if meal not found
    }

    return $result->fetch_assoc();
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------
$mealId = isset($_GET['meal_id']) ? $_GET['meal_id'] : null;

if (!$mealId)
{
    $_SESSION['error_message'] = "No meal specified.";
    header("Location: meal_records.php");
    exit;
}

$mealDetails = getMealDetails($conn, $mealId);

if (!$mealDetails || empty($mealDetails['food_fdcId']))
{
    $_SESSION['error_message'] = "Meal not found or no foods added to this meal yet.";
    header("Location: meal_records.php");
    exit;
}

$foodFdcids = json_decode($mealDetails['food_fdcId'], true);
$foods = [];

foreach ($foodFdcids as $fdcId)
{
    $foodData = fetchFoodDetails($fdcId);

    if ($foodData === null || !isset($foodData['description']))
    {
        $_SESSION['error_message'] = "Error fetching food details for FDC ID: $fdcId";
        continue;
    }

    $foods[] = $foodData;
}

$totalNutrients = sumNutrients($foods);

// Variables for display
$mealName = htmlspecialchars($mealDetails['name']);
$mealCreatedAt = htmlspecialchars($mealDetails['created_at']);
$foodNames = array_column($foods, 'description'); // Extract food names

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Details - <?= $mealName ?></title>
    <?php include '../bootstrap.html'; ?>
</head>

<body>
    <div class="container">
        <h1>Meal Details: <?= $mealName ?></h1>
        <p>Created on: <?= $mealCreatedAt ?></p>
        <p>Food Items:</p>
        <ul>
            <?php foreach ($foodNames as $foodName) : ?>
                <li><?= htmlspecialchars($foodName) ?></li>
            <?php endforeach; ?>
        </ul>
        <hr>

        <!-- Display back link -->
        <a href="meal_functions/meal_records.php" class="btn btn-secondary mb-3">Back to Meal Records</a>

        <!-- Display total nutrients table -->
        <div id="totalNutrients">
            <h2>Total Nutrients</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nutrient Name</th>
                        <th>Total Amount</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($totalNutrients as $nutrient) : ?>
                        <tr>
                            <td><?= htmlspecialchars($nutrient['name']) ?></td>
                            <td><?= htmlspecialchars($nutrient['amount']) ?></td>
                            <td><?= htmlspecialchars($nutrient['unitName']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Display error message if set -->
        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

    </div>
</body>

</html>