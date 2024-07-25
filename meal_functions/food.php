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

// Function to fetch ingredient alerts from the database
function getIngredientAlerts($conn, $user_id)
{
    $sql = "SELECT alerts FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
        $alerts = json_decode($row['alerts'], true);
        return is_array($alerts) ? array_map('trim', $alerts) : [];
    }

    return [];
}

// Function to highlight specific ingredients in red and ensure they are in uppercase
function highlightIngredients($ingredients, $highlightedIngredients)
{
    $ingredients = strtoupper($ingredients);
    foreach ($highlightedIngredients as $ingredient)
    {
        $ingredient = strtoupper($ingredient);
        // Use preg_replace to replace all occurrences of the ingredient with the highlighted version
        $ingredients = preg_replace('/(' . preg_quote($ingredient, '/') . ')/i', '<span class="highlight">$1</span>', $ingredients);
    }
    return $ingredients;
}

// Function to render food details
function displayFoodDetails($data, $highlightedIngredients)
{
    global $conn; // Make sure the database connection is accessible here

    echo "<div class='container mt-4'>";
    echo "<div class='col-md-10 mx-auto'>"; // Centering and limiting width

    // Form to add FDC ID to current meal
    echo "<form method='post'>";
    echo "<input type='hidden' name='fdcId' value='" . htmlspecialchars($data['fdcId'] ?? '---') . "'>";
    echo "<button type='submit' class='btn btn-primary'>Add to Current Meal</button>";
    echo "</form>";

    echo "<hr>";
    echo "<h1 class='display-5'>" . htmlspecialchars($data['description'] ?? '---') . "</h1>";
    echo "<ul>";
    echo "<li><strong>FDC ID: </strong>" . htmlspecialchars($data['fdcId'] ?? '---') . "</li>";
    echo "<li><strong>Data Type: </strong>" . htmlspecialchars($data['dataType'] ?? '---') . "</li>";
    echo "</ul>";
    echo "<hr>";

    // Display ingredients in a table if available
    if (isset($data['ingredients']) && !empty($data['ingredients']))
    {
        echo "<h2 class='display-6'>Ingredients</h2>";
        $ingredientsArray = explode(', ', $data['ingredients']); // Split ingredients by comma and space
        echo "<table class='table table-striped'>";
        echo "<thead><tr><th class='col-7'>Ingredient</th><th class='col-1' >Action</th></tr></thead>";
        echo "<tbody>";
        foreach ($ingredientsArray as $ingredient)
        {
            $highlightedIngredient = highlightIngredients($ingredient, $highlightedIngredients);
            echo "<tr class='ingredient-row'>";
            echo "<td>$highlightedIngredient</td>";
            echo "<td>";
            // Create a form for each ingredient with an "Add" button
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='ingredient' value='" . htmlspecialchars($ingredient) . "'>";
            echo "<button type='submit' class='btn btn-success btn-sm' style='height: 25px;'>Add</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
    else
    {
        echo "<h2 class='display-6'>Ingredients</h2>";
        echo "<p>No ingredients available.</p>";
    }

    // Display nutrients in a table
    echo "<h2 class='display-6'>Food Nutrients</h2>";
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

// Function to handle adding ingredients to alerts
function addIngredientToAlerts($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredient']))
    {
        // Check if user is logged in
        if (!isset($_SESSION['id']))
        {
            $_SESSION['error_message'] = "You must be logged in to add ingredients to alerts.";
            return;
        }

        $ingredient = $conn->real_escape_string(trim($_POST['ingredient']));
        $user_id = $_SESSION['id'];

        // Fetch the current alerts from the database
        $sql = "SELECT alerts FROM users WHERE id = $user_id";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $alerts = json_decode($row['alerts'], true);
            $alerts = is_array($alerts) ? array_map('trim', $alerts) : [];

            // Add the new ingredient to the alerts array
            if (!in_array($ingredient, $alerts))
            {
                $alerts[] = $ingredient;
                $alertsJson = json_encode($alerts);
                $sqlUpdate = "UPDATE users SET alerts = '$alertsJson' WHERE id = $user_id";

                if ($conn->query($sqlUpdate) === TRUE)
                {
                    $_SESSION['success_message'] = "Ingredient added to your alerts.";
                }
                else
                {
                    $_SESSION['error_message'] = "Error updating alerts: " . $conn->error;
                }
            }
            else
            {
                $_SESSION['error_message'] = "Ingredient is already in your alerts.";
            }
        }
        else
        {
            $_SESSION['error_message'] = "User not found.";
        }
    }
}

// Function to fetch and display food details
function fetchAndDisplayDetails($conn, $fdcId)
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

    // Get ingredient alerts for the logged-in user
    $highlightedIngredients = isset($_SESSION['id']) ? getIngredientAlerts($conn, $_SESSION['id']) : [];

    displayFoodDetails($data, $highlightedIngredients);
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------

// Handle form submission for adding FDC ID to current meal
addFdcIdToMeal($conn);

// Handle form submission for adding ingredient to alerts
addIngredientToAlerts($conn);

// Get the fdcId from the URL parameter
$fdcId = isset($_GET['fdcId']) ? urlencode($_GET['fdcId']) : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Details</title>
    <?php include '../bootstrap.html'; ?>
</head>

<body>
    <div class="container mt-4">
        <div class="col-md-10 mx-auto">
            <?php
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

            // Check if food item is specified
            if (!$fdcId)
            {
                echo "<div class='alert alert-danger'>No food item specified.</div>";
                return;
            }

            // Fetch and display food details
            fetchAndDisplayDetails($conn, $fdcId);
            ?>
        </div>
    </div>
</body>

</html>