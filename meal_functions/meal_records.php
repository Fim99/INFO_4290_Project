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
                // Update current meal name in session
                $_SESSION['current_meal_name'] = $newMealName;
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
            // Update current meal name in session
            $_SESSION['current_meal_name'] = getCurrentMealDetails($conn, $_SESSION['current_meal_id']);
            $_SESSION['success_message'] = "Current meal selected successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if (isset($_POST['delete_meal']))
        {
            $mealId = $_POST['delete_meal'];
            if (deleteMeal($conn, $mealId))
            {
                $_SESSION['success_message'] = "Meal deleted successfully.";

                // Check if the deleted meal was the current selected meal
                if ($_SESSION['current_meal_id'] == $mealId)
                {
                    // Get the next most recent meal
                    $meals = getUserMeals($conn, $_SESSION['id']);
                    if (!empty($meals))
                    {
                        $_SESSION['current_meal_id'] = $meals[0]['id'];
                        // Update current meal name in session
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
    }
}

// ------ MAIN CODE START WHERE METHODS ARE CALLED -------

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
    <div class="container">
        <h1>Meal Records</h1>

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

        <!-- Form to set current meal -->
        <form method="post">
            <div class="form-group">
                <label for="setCurrentMeal">Select Meal to Modify (Currently:
                    <?php echo htmlspecialchars($currentMealName); ?>)</label>
                <select class="form-control" id="setCurrentMeal" name="set_current_meal">
                    <?php foreach ($meals as $meal) : ?>
                        <option value="<?= htmlspecialchars($meal['id']) ?>" <?= ($_SESSION['current_meal_id'] == $meal['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($meal['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Set Current Meal</button>
        </form>

        <hr>

        <!-- Form to create new meal -->
        <form method="post">
            <div class="form-group">
                <label for="newMealName">Create New Meal</label>
                <input type="text" class="form-control" id="newMealName" name="new_meal_name"
                    placeholder="Enter meal name">
            </div>
            <button type="submit" class="btn btn-success">Create Meal</button>
        </form>

        <!-- Display table of meals -->
        <h2>Meals List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Meal Name</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meals as $meal) : ?>
                    <tr>
                        <td><a
                                href="meal_functions/meal_details.php?meal_id=<?= htmlspecialchars($meal['id']) ?>"><?= htmlspecialchars($meal['name']) ?></a>
                        </td>
                        <td><?= htmlspecialchars($meal['created_at']) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this meal?');">
                                <input type="hidden" name="delete_meal" value="<?= htmlspecialchars($meal['id']) ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>