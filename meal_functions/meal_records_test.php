<?php
session_start();
include '../check_loggin.php';
include 'api.php';
include '../bootstrap.html';

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
function getUserDetails($conn, $userId)
{
    $sql = "SELECT * FROM users WHERE id = $userId";
    $result = $conn->query($sql);
    if ($result->num_rows > 0)
    {
        return $result->fetch_assoc(); // Fetches the user details as an associative array
    }
    else
    {
        return null;
    }
}

function getUserMeals($userId)
{
    // This is a placeholder. Replace with database query.
    return
        [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
            ['id' => 3, 'name' => 'test3']
        ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['new_meal_name']))
    {
        $newMealName = $_POST['new_meal_name'];
        // Add the new meal to the database
        // This is a placeholder
    }

    if (isset($_POST['current_meal']))
    {
        $currentMealId = $_POST['current_meal'];
        // Set the current meal in the database or session
        // This is a placeholder
    }

    if (isset($_POST['delete_session']))
    {
        // Destroy the session
        session_destroy();

        // Rederect to Login
        header("Location: ../login.php");
        exit;
    }
}

$userDetails = getUserDetails($conn, $id);
$meals = getUserMeals($id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Records</title>
</head>

<body>
    <div class="container">
        <h1>Meal Records</h1>
        <!-- Display session variables -->
        <h2>Session Variables</h2>
        <pre><?php print_r($_SESSION); ?></pre>
        <pre><?php echo htmlspecialchars($userDetails['email']); ?></pre> <!-- Displaying username -->
        <pre><?php echo htmlspecialchars($userDetails['username']); ?></pre> <!-- Displaying username -->
        <pre><?php echo htmlspecialchars($userDetails['id']); ?></pre> <!-- Displaying username -->

        <form method="post">
            <div class="form-group">
                <label for="currentMeal">Select Current Meal</label>
                <select class="form-control" id="currentMeal" name="current_meal">
                    <?php foreach ($meals as $meal) : ?>
                        <option value="<?= htmlspecialchars($meal['id']) ?>"><?= htmlspecialchars($meal['name']) ?></option>
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
            <button type="submit" class="btn btn-danger" name="delete_session">Delete Session</button> <!-- Destroy Session -->
        </form>
    </div>
</body>

</html>