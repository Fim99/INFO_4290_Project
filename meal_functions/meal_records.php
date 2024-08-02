<?php
include_once '../nav.php';
require_once '../account_functions/check_loggin.php';
require_once 'api.php';
include_once '../account_functions/db_connection.php';

// Function to get user details by user ID
function getUserDetails($conn, $user_id)
{
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

// Function to get user meals by user ID
function getUserMeals($conn, $user_id)
{
    $meals = [];
    $sql = "SELECT id, name, created_at FROM meals WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0)
    {
        while ($row = $result->fetch_assoc())
        {
            $meals[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'created_at' => $row['created_at']
            ];
        }
    }

    return $meals;
}

// Function to get current meal details by meal ID
function getCurrentMealDetails($conn, $meal_id)
{
    $sql = "SELECT name FROM meals WHERE id = $meal_id";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['name'] : "None Selected";
}

// Function to add a new meal for the current user
function addNewMeal($conn, $user_id, $meal_name)
{
    $escaped_meal_name = $conn->real_escape_string($meal_name);
    $created_at = date('Y-m-d H:i:s');
    $sql = "INSERT INTO meals (user_id, name, food_fdcid, created_at) VALUES ('$user_id', '$escaped_meal_name', '[]', '$created_at')";

    if ($conn->query($sql) === TRUE)
    {
        $_SESSION['current_meal_id'] = $conn->insert_id;
        return true;
    }

    return false;
}

// Function to update meal name
function updateMealName($conn, $meal_id, $new_name)
{
    $escaped_new_name = $conn->real_escape_string($new_name);
    $sql = "UPDATE meals SET name = '$escaped_new_name' WHERE id = $meal_id";
    return $conn->query($sql);
}

// Function to duplicate a meal and its food items
function duplicateMeal($conn, $meal_id, $user_id)
{
    // Fetch the meal details
    $sql = "SELECT name, food_fdcid FROM meals WHERE id = $meal_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0)
    {
        $meal = $result->fetch_assoc();
        $meal_name = $meal['name'];
        $food_fdcid = $meal['food_fdcid'];

        // Create a new meal with the same name and food items but with a new timestamp
        $escaped_meal_name = $conn->real_escape_string($meal_name);
        $created_at = date('Y-m-d H:i:s');
        $sql = "INSERT INTO meals (user_id, name, food_fdcid, created_at) VALUES ('$user_id', '$escaped_meal_name', '$food_fdcid', '$created_at')";

        if ($conn->query($sql) === TRUE)
        {
            $_SESSION['current_meal_id'] = $conn->insert_id;
            $_SESSION['current_meal_name'] = $meal_name; // Set current meal name
            return true;
        }
    }

    return false;
}

function renderMealRecords($meals)
{
    $previousDay = '';

    foreach ($meals as $meal)
    {
        $mealDate = new DateTime($meal['created_at']);
        $currentDay = $mealDate->format('l, F j'); // Format: Day of the week, Month day

        if ($currentDay !== $previousDay)
        {
            echo '<tr class="table fw-bold"><td colspan="2" style="background-color: var(--bs-primary); color: white;">' . $currentDay . '</td></tr>';
            $previousDay = $currentDay;
        }

        $formattedDate = $mealDate->format('Y-m-d H:i:s');
        echo '<tr>';
        echo '<td><a href="meal_functions/meal_details.php?meal_id=' . htmlspecialchars($meal['id']) . '">' . htmlspecialchars($meal['name']) . '</a> <span class="text-muted">(' . $formattedDate . ')</span></td>';
        echo '<td class="text-center col-3">
                <form action="' . $_SERVER['PHP_SELF'] . '" method="POST" style="display:inline-block;">
                    <button type="submit" name="set_current_meal" value="' . $meal['id'] . '" class="btn btn-primary">Select</button>
                </form>
                <form action="' . $_SERVER['PHP_SELF'] . '" method="POST" style="display:inline-block;">
                    <button type="submit" name="delete_meal" value="' . $meal['id'] . '" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this meal?\')">Delete</button>
                </form>
                <form action="' . $_SERVER['PHP_SELF'] . '" method="POST" style="display:inline-block;">
                    <button type="submit" name="reuse_meal" value="' . $meal['id'] . '" class="btn btn-success">Reuse</button>
                </form>
              </td>';
        echo '</tr>';
    }
}

// Function to delete a meal from the database
function deleteMeal($conn, $meal_id)
{
    $sql = "DELETE FROM meals WHERE id = $meal_id";
    return $conn->query($sql);
}

// Handle form submissions
function handleFormSubmissions($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (isset($_POST['new_meal_name']))
        {
            $newMealName = $_POST['new_meal_name'];
            if (empty($newMealName))
            {
                $_SESSION['error_message'] = "Meal name cannot be empty.";
            }
            elseif (addNewMeal($conn, $_SESSION['id'], $newMealName))
            {
                $_SESSION['success_message'] = "Meal added successfully.";
                $_SESSION['current_meal_name'] = $newMealName; // Update current meal name in session
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            else
            {
                $_SESSION['error_message'] = "Error adding new meal.";
            }
        }

        if (isset($_POST['set_current_meal']))
        {
            $_SESSION['current_meal_id'] = $_POST['set_current_meal'];
            $_SESSION['current_meal_name'] = getCurrentMealDetails($conn, $_SESSION['current_meal_id']);
            $_SESSION['success_message'] = "Current meal changed successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if (isset($_POST['delete_meal']))
        {
            $mealId = $_POST['delete_meal'];
            if (deleteMeal($conn, $mealId))
            {
                $_SESSION['success_message'] = "Meal deleted successfully.";

                if ($_SESSION['current_meal_id'] == $mealId)
                {
                    $meals = getUserMeals($conn, $_SESSION['id']);
                    if (!empty($meals))
                    {
                        $_SESSION['current_meal_id'] = $meals[0]['id'];
                        $_SESSION['current_meal_name'] = $meals[0]['name'];
                    }
                    else
                    {
                        unset($_SESSION['current_meal_id']);
                        unset($_SESSION['current_meal_name']);
                    }
                }

                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            else
            {
                $_SESSION['error_message'] = "Error deleting meal.";
            }
        }

        if (isset($_POST['reuse_meal']))
        {
            $mealId = $_POST['reuse_meal'];
            if (duplicateMeal($conn, $mealId, $_SESSION['id']))
            {
                $_SESSION['success_message'] = "Meal reused successfully.";
                // Update current meal name in session after reusing meal
                $_SESSION['current_meal_name'] = getCurrentMealDetails($conn, $_SESSION['current_meal_id']);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            else
            {
                $_SESSION['error_message'] = "Error reusing meal.";
            }
        }

        if (isset($_POST['update_meal_name']))
        {
            $newMealName = $_POST['updated_meal_name'];
            $mealId = $_SESSION['current_meal_id'];
            if (!empty($newMealName))
            {
                if (updateMealName($conn, $mealId, $newMealName))
                {
                    $_SESSION['success_message'] = "Meal name updated successfully.";
                    $_SESSION['current_meal_name'] = $newMealName; // Update current meal name in session
                }
                else
                {
                    $_SESSION['error_message'] = "Error updating meal name.";
                }
            }
            else
            {
                $_SESSION['error_message'] = "Meal name cannot be empty.";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Main code where methods are called
handleFormSubmissions($conn);
$userDetails = getUserDetails($conn, $_SESSION['id']);
$meals = getUserMeals($conn, $_SESSION['id']);
$currentMealName = isset($_SESSION['current_meal_id']) ? getCurrentMealDetails($conn, $_SESSION['current_meal_id']) : "None Selected";

// Update current meal name in session initially
if (isset($_SESSION['current_meal_id']))
{
    $_SESSION['current_meal_name'] = $currentMealName;
}
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
            <h1 class='display-5'>Meal Records</h1>
            <hr>

            <!-- Display success message if set -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- Display error message if set -->
            <?php if (isset($_SESSION['error_message'])) : ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Form to create new meal -->
            <form method="post" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="newMealName">Create New Meal</label>
                            <input type="text" class="form-control" id="newMealName" maxlength="50" name="new_meal_name"
                                placeholder="Enter meal name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success btn-block mt-4">Create Meal</button>
                    </div>
                </div>
            </form>

            <!-- Form to update current meal name -->
            <form method="post" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="updatedMealName">Rename Currently Selected Meal </label>
                            <input type="text" class="form-control" id="updatedMealName" maxlength="50"
                                name="updated_meal_name" placeholder="Enter new meal name"
                                value="<?= htmlspecialchars($_SESSION['current_meal_name']) ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="update_meal_name"
                            class="btn btn-secondary btn-block mt-4">Rename</button>
                    </div>
                </div>
            </form>

            <!-- Table to display meal records with day separators -->
            <table class="table nutrient-table">
                <thead>
                    <tr>
                        <th>Meal Name</th>
                        <th class="text-center">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php renderMealRecords($meals); // Call the function to render meal records ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
