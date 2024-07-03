<?php
	session_start();

	// To-Do: Verify user is logged in and redirect if not.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information</title>
    <?php include 'bootstrap.html'?>
</head>

<body>
	<h1>Account Information</h1>
	<p>Email: <?php echo "placeholder@email.com" ?> <a href="login.php">Change</a><br> 
	Username: <?php echo "place_holder" ?> <a href="login.php">Change</a> </p>
	<p><a href="change_password.php">Change password</a></p>
</body>






