<?php
// Set current user using user_id
$_SESSION['id'] = $row['id'];

$user_id = $_SESSION['id']; 

$sql_last_meal = "SELECT id FROM meals WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 1";

$result_last_meal = $conn->query($sql_last_meal);

// Retrieve the last inserted meal ID for the user
if ($result_last_meal && $result_last_meal->num_rows > 0)
{
    $last_meal = $result_last_meal->fetch_assoc();
    $_SESSION['current_meal_id'] = $last_meal['id']; // Set the current meal ID in session
}
else
{
    $_SESSION['current_meal_id'] = NULL; // No meals found, set current meal ID to null
}
?>