<?php

include '../nav.php';
include '../account_functions/check_loggin.php';
include 'api.php';
include '../account_functions/db_connection.php';

include 'nutrients_array.php';

// Function to build API URL
function buildApiUrl($fdcIds)
{
    $ids = implode('&fdcIds=', $fdcIds);
    return "https://api.nal.usda.gov/fdc/v1/foods?fdcIds=$ids";
}

// Function to fetch and decode API response
function fetchApiData($url)
{
    $response = fetchDataFromAPI($url);
    return $response ? json_decode($response, true) : null;
}

// Function to fetch food details using API
function fetchFoodDetails($fdcIds)
{
    $url = buildApiUrl($fdcIds);
    $data = fetchApiData($url);
    return $data;
}

// Function to sum up nutrient values across all foods in a meal
function sumNutrients($foods, $selectedNutrientIds, $nutrientTypes)
{
    $totalNutrients = extractNutrientTotals($foods, $selectedNutrientIds);
    return organizeNutrientsByType($totalNutrients, $nutrientTypes);
}

// Extracts and sums up nutrient values from food data
function extractNutrientTotals($foods, $selectedNutrientIds)
{
    $totalNutrients = [];

    foreach ($foods as $food)
    {
        foreach ($food['foodNutrients'] as $nutrient)
        {
            $nutrientId = $nutrient['nutrient']['id'];

            if (in_array($nutrientId, $selectedNutrientIds))
            {
                $amount = $nutrient['amount'] ?? 0;

                if (!isset($totalNutrients[$nutrientId]))
                {
                    $totalNutrients[$nutrientId] = [
                        'name' => $nutrient['nutrient']['name'],
                        'amount' => 0,
                        'unitName' => $nutrient['nutrient']['unitName']
                    ];
                }

                $totalNutrients[$nutrientId]['amount'] += $amount;
            }
        }
    }

    return $totalNutrients;
}

// Organizes nutrients into categories based on nutrient types
function organizeNutrientsByType($totalNutrients, $nutrientTypes)
{
    $sortedNutrients = [];

    foreach ($nutrientTypes as $type => $nutrients)
    {
        foreach ($nutrients as $name => $id)
        {
            if (isset($totalNutrients[$id]))
            {
                $sortedNutrients[$type][] = [
                    'name' => $totalNutrients[$id]['name'],
                    'amount' => $totalNutrients[$id]['amount'],
                    'unitName' => $totalNutrients[$id]['unitName']
                ];
            }
        }
    }

    return $sortedNutrients;
}

// Function to retrieve meal details from the database
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

// Function to remove a food from the meal
function removeFromMeal($conn, $mealId, $fdcId)
{
    $sql = "SELECT food_fdcId FROM meals WHERE id = $mealId";
    $result = $conn->query($sql);

    if (!$result || $result->num_rows === 0)
    {
        return false; // Meal not found
    }

    $row = $result->fetch_assoc();
    $foodFdcIds = json_decode($row['food_fdcId'], true);

    // Find and remove the food FDC ID from the array
    $key = array_search($fdcId, $foodFdcIds);
    if ($key !== false)
    {
        unset($foodFdcIds[$key]);
    }

    // Update the meal record with the modified food list
    $updatedFoodFdcIds = json_encode(array_values($foodFdcIds));
    $updateSql = "UPDATE meals SET food_fdcId = '$updatedFoodFdcIds' WHERE id = $mealId";
    $updateResult = $conn->query($updateSql);

    return $updateResult;
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

$foods = fetchFoodDetails($foodFdcids);

if (is_null($foods) || !is_array($foods))
{
    $_SESSION['error_message'] = "Error fetching food details.";
    header("Location: meal_records.php");
    exit;
}

$totalNutrients = sumNutrients($foods, $selectedNutrientIds, $nutrientTypes);

// Variables for display
$mealName = htmlspecialchars($mealDetails['name']);
$mealCreatedAt = htmlspecialchars($mealDetails['created_at']);
$foodDetails = []; // Array to hold food details for the table

// Prepare food details for the table
foreach ($foods as $food)
{
    $foodDetails[] = [
        'fdcId' => $food['fdcId'],
        'name' => htmlspecialchars($food['description']),
        'category' => htmlspecialchars($food['dataType'])
    ];
}

// Check if a remove action is requested
if (isset($_POST['remove_fdc_id']))
{
    $removeFdcId = $_POST['remove_fdc_id'];
    $removeResult = removeFromMeal($conn, $mealId, $removeFdcId);
    if ($removeResult)
    {
        $_SESSION['success_message'] = "Food removed successfully from the meal.";
        // Redirect to refresh the page and prevent form resubmission
        header("Location: meal_details.php?meal_id=$mealId");
        exit;
    }
    else
    {
        $_SESSION['error_message'] = "Failed to remove food from the meal.";
    }
}

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
    <div class="container mt-4">
        <div class="col-md-10 mx-auto">
            <a href="meal_functions/meal_records.php" class="btn btn-secondary">Back to Meal Records</a>
            <hr>
            <h1><?= $mealName ?></h1>
            <p>Created on: <?= $mealCreatedAt ?></p>

            <!-- Display success or error message if set -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php elseif (isset($_SESSION['error_message'])) : ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message'] ?>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Display food items in a table -->
            <div id="foodItems">
                <h2>Food Items</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="col-5">Food Name</th>
                            <th class="col-3">Category</th>
                            <th class="col-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($foodDetails as $food) : ?>
                            <tr>
                                <td><a href="meal_functions/food.php?fdcId=<?= $food['fdcId'] ?>"><?= $food['name'] ?></a></td>
                                <td><?= $food['category'] ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="remove_fdc_id" value="<?= $food['fdcId'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <!-- Display total nutrients table -->
            <div id="totalNutrients">
                <h2>Total Nutrients</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="col-5">Nutrient Name</th>
                            <th class="col-3">Total Amount</th>
                            <th class="col-1">Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($totalNutrients as $type => $nutrients) : ?>
                            <!-- Category Row -->
                            <tr>
                                <td colspan="3"><strong><?= ucfirst($type) ?></strong></td>
                            </tr>
                            <?php foreach ($nutrients as $nutrient) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($nutrient['name']) ?></td>
                                    <td><?= htmlspecialchars($nutrient['amount']) ?></td>
                                    <td><?= htmlspecialchars($nutrient['unitName']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
