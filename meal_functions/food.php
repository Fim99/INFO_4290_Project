<?php
include '../nav.php';
include 'api.php';
include '../account_functions/db_connection.php';

// Function to build the API URL for fetching food details
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

// Function to render food details
function displayFoodDetails($data)
{
    echo "<div class='container mt-4'>";
    echo "<div class='col-md-10 mx-auto'>"; // Centering and limiting width

    // Form to add FDC ID to current meal
    echo "<form method='post'>";
    echo "<input type='hidden' name='fdcId' value='" . htmlspecialchars($data['fdcId'] ?? '---') . "'>";
    echo "<button type='submit' class='btn btn-primary'>Add to Current Meal</button>";
    echo "</form>";

    echo "<hr>";
    echo "<h1>" . htmlspecialchars($data['description'] ?? '---') . "</h1>";
    echo "<ul>";
    echo "<li><strong>FDC ID: </strong>" . htmlspecialchars($data['fdcId'] ?? '---') . "</li>";
    echo "<li><strong>Data Type: </strong>" . htmlspecialchars($data['dataType'] ?? '---') . "</li>";
    echo "</ul>";
    echo "<hr>";

    // Display ingredients if available
    if (isset($data['ingredients']) && !empty($data['ingredients']))
    {
        echo "<h2>Ingredients</h2>";
        echo "<ul>";
        echo "<li>" . htmlspecialchars($data['ingredients']) . "</li>";
        echo "</ul>";
        echo "<hr>";
    }
    else
    {
        echo "<h2>Ingredients</h2>";
        echo "<p>No ingredients available.</p>";
    }

    // Display nutrients in a table
    echo "<h2>Food Nutrients</h2>";
    echo "<div class='col-md-12'>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th class='col-5'>Nutrient Name</th><th class='col-3'>Amount</th><th class='col-1'>Unit</th></tr></thead>";
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

    echo "</div>";
    echo "</div>";
}

// Function to handle adding FDC ID to the current meal
function addFdcIdToMeal($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fdcId']))
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
                // Redirect with the fdcId parameter
                header("Location: " . $_SERVER['PHP_SELF'] . "?fdcId=" . urlencode($fdcId));
                exit;
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

// Function to fetch and display food details
function fetchAndDisplayDetails($fdcId)
{
    $url = buildApiUrl($fdcId);
    $data = fetchApiData($url);

    if ($data === null)
    {
        $_SESSION['error_message'] = "An error occurred while fetching data from the API.";
        return;
    }

    if (!isset($data['description']))
    {
        $_SESSION['error_message'] = "No details found for this food item.";
        return;
    }

    if (!isset($data['foodNutrients']) || !is_array($data['foodNutrients']) || count($data['foodNutrients']) === 0)
    {
        $_SESSION['error_message'] = "No nutrient information available.";
        return;
    }

    displayFoodDetails($data);
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------

// Handle form submission
addFdcIdToMeal($conn);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Records</title>
    <?php include '../bootstrap.html'; ?>
</head>
<body>
    <div class="container mt-4">
        <div class="col-md-10 mx-auto">
            <?php
            // Display success message if set
            if (isset($_SESSION['success_message'])) {
                echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
                unset($_SESSION['success_message']);
            }

            // Display error message if set
            if (isset($_SESSION['error_message'])) {
                echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
                unset($_SESSION['error_message']);
            }

            // Check if food item is specified
            if (!isset($_GET['fdcId'])) {
                echo "<div class='alert alert-danger'>No food item specified.</div>";
                return;
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
// Get the fdcId from the URL parameter and display the food details
$fdcId = urlencode($_GET['fdcId']);
fetchAndDisplayDetails($fdcId);
?>
