<?php
// Initialize session variables and fetch user details
initializeSession($row['id'], $conn);

// Function to initialize session variables
function initializeSession($user_id, $conn)
{
    $_SESSION['id'] = $user_id;

    // Fetch user details
    $sql_user_details = "SELECT username FROM users WHERE id = $user_id";
    $result_user_details = $conn->query($sql_user_details);

    if ($result_user_details && $result_user_details->num_rows > 0)
    {
        $user_details = $result_user_details->fetch_assoc();
        $_SESSION['username'] = $user_details['username']; // Set the username in session
    }
    else
    {
        $_SESSION['username'] = "Guest"; // Default value or handle as needed
    }

    // Retrieve the last inserted meal ID for the user
    $_SESSION['current_meal_id'] = getLastMealId($user_id, $conn);

    // Set current meal name in session
    $_SESSION['current_meal_name'] = getCurrentMealName($_SESSION['current_meal_id'], $conn);
}

// Function to get the last inserted meal ID for the user
function getLastMealId($user_id, $conn)
{
    $sql_last_meal = "SELECT id FROM meals WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1";
    $result_last_meal = $conn->query($sql_last_meal);

    if ($result_last_meal && $result_last_meal->num_rows > 0)
    {
        $last_meal = $result_last_meal->fetch_assoc();
        return $last_meal['id'];
    }

    return null; // No meals found, return null
}

// Function to get current meal details by meal ID
function getCurrentMealName($meal_id, $conn)
{
    if ($meal_id === null)
    {
        return "None Selected";
    }

    $sql = "SELECT name FROM meals WHERE id = $meal_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0)
    {
        $meal_details = $result->fetch_assoc();
        return $meal_details['name'];
    }

    return "None Selected"; // Handle if meal details are not found
}

// No need to fetch current meal name here as it's already set during initialization
$currentMealName = $_SESSION['current_meal_name'];
?>