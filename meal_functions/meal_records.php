<?php
session_start();

include '../bootstrap.html';
include '../nav.php';
include '../account_functions/check_loggin.php';
include 'api.php';

$sql_servername = "localhost";
$sql_username = "root";
$sql_password = "";
$sql_dbname = "nutritional_tracker";

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
    if ($result && $result->num_rows > 0)
    {
        return $result->fetch_assoc(); // Fetches the user details as an associative array
    }
    else
    {
        return null;
    }
}

// Function to get user meals by user ID
function getUserMeals($conn, $user_id)
{
    $meals = [];

    // Prepare SQL statement to fetch meals ordered by created_at in descending order
    $sql = "SELECT id, name, created_at FROM meals WHERE user_id = $user_id ORDER BY created_at DESC";

    // Execute query
    $result = $conn->query($sql);

    // Check if query executed successfully
    if ($result && $result->num_rows > 0)
    {
        // Fetch all rows into an associative array
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
    // Prepare SQL statement to fetch meal based on current meal
    $sql = "SELECT name FROM meals WHERE id = $meal_id";

    // Execute query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    else
    {
        return "None Selected";
    }
}

// Function to add a new meal for the current user
function addNewMeal($conn, $user_id, $meal_name)
{
    // Escape the meal name to prevent SQL injection
    $escaped_meal_name = $conn->real_escape_string($meal_name);

    // Values for the new meal
    $id = NULL; // Auto-incremented
    $food_fdcid = json_encode(['Currently Empty']); // Example JSON format
    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Prepare SQL statement with values
    $sql = "INSERT INTO meals (id, user_id, name, food_fdcid, created_at) VALUES ('$id', '$user_id', '$escaped_meal_name', '$food_fdcid', '$created_at')";

    // Execute query
    if ($conn->query($sql) === TRUE)
    {
        // Get the ID of the newly inserted meal
        $new_meal_id = $conn->insert_id;

        // Set the newly inserted meal as the current meal
        $_SESSION['current_meal_id'] = $new_meal_id;

        return true; // Successfully inserted
    }
    else
    {
        return false; // Error inserting
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['new_meal_name']))
    {
        $newMealName = $_POST['new_meal_name'];

        // Check if the meal name is empty
        if (empty($newMealName))
        {
            echo "Meal name cannot be empty.";
            exit; // Exit early if meal name is empty
        }

        // Attempt to add the new meal
        $added = addNewMeal($conn, $_SESSION['id'], $newMealName);

        if ($added)
        {
            // Refresh the page or perform any additional actions upon success
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        else
        {
            echo "Error adding new meal.";
            exit; // Exit early on error
        }
    }

    // Handle setting current meal
    if (isset($_POST['set_current_meal']))
    {
        $currentMealId = $_POST['set_current_meal'];
        $_SESSION['current_meal_id'] = $currentMealId;
    }

    // Handle deleting session
    if (isset($_POST['delete_session']))
    {
        // Destroy the session
        session_destroy();

        // Redirect to Login
        header("Location: ../account_functions/login.php");
        exit;
    }
}

// Get user details by session user_id (assuming it's set)
$userDetails = getUserDetails($conn, $_SESSION['id']);

// Get user meals by user ID
$meals = getUserMeals($conn, $_SESSION['id']);

// Get current meal name if session variable is set
$currentMealName = isset($_SESSION['current_meal_id']) ? getCurrentMealDetails($conn, $_SESSION['current_meal_id']) : "None Selected";
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

        <!-- Display session variables -->
        <h2>Session Variables</h2>
        <pre><?php print_r($_SESSION); ?></pre>
        <pre><?php echo htmlspecialchars($userDetails['email']); ?></pre> <!-- Displaying email -->
        <pre><?php echo htmlspecialchars($userDetails['username']); ?></pre> <!-- Displaying username -->
        <pre><?php echo htmlspecialchars($userDetails['id']); ?></pre> <!-- Displaying user ID -->

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

        <form method="post">
            <div class="form-group">
                <label for="newMealName">Create New Meal</label>
                <input type="text" class="form-control" id="newMealName" name="new_meal_name"
                    placeholder="Enter meal name">
            </div>
            <button type="submit" class="btn btn-success">Create Meal</button>
        </form>

        <!-- Form for Delete Session button -->
        <form method="post">
            <button type="submit" class="btn btn-danger" name="delete_session">Delete Session</button>
            <!-- Destroy Session -->
        </form>

        <!-- Display table of meals -->
        <h2>Meals List</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Meal Name</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meals as $meal) : ?>
                    <tr>
                        <td><?= htmlspecialchars($meal['name']) ?></td>
                        <td><?= htmlspecialchars($meal['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>