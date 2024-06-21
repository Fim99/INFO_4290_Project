<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <?php include 'bootstrap.html'?>
</head>

<body>
	<h1>Account Registration</h1>
	<form name="account_creation" method="post" action="">
		<div class="col-sm-6 mb-2">
			<label for="email" class="form-label">Email address</label>
			<input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" required>
		</div>
		<div class="col-sm-6 mb-2">
			<label for="username" class="form-label">Username</label>
			<input type="text" name="username" class="form-control" id="username" required>
		</div>
		<div class="col-sm-6 mb-2">
			<label for="password" class="form-label">Password</label>
			<input type="password" name="password" class="form-control" id="password" required>
		</div>
		<div class="col-sm-6 mb-2">
			<label for="confirm_password1" class="form-label">Confirm Password</label>
			<input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
		</div>
		<button type="submit" name="submit" class="btn btn-primary">Submit</button>
	</form>
</body>

<?php
	$sql_servername = "localhost";
	$sql_username = "root";
	$sql_password = "";
	$sql_dbname = "nutritional_tracker";

	// Create connection
	$conn = new mysqli($sql_servername, $sql_username, $sql_password, $sql_dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	if(isset($_POST["submit"]))
	{
		$email = $_POST["email"];
		$username = $_POST["username"];
		$password = $_POST["password"];
		$confirm_password = $_POST["confirm_password"];

		$valid_input = true;

		// Check if the inputted passwords match.
		if ($password != $confirm_password)
		{
			echo "<p>Passwords do not match.</p>";
			$valid_input = false;
		}

		// Sanitize the email and username input.
		// No need to sanatize the password, as it will be hashed.
		$email = mysqli_real_escape_string($conn, $email);
		$username = mysqli_real_escape_string($conn, $username);

		
		// Hash password for database storage
		// Note: To check if a password matches a hash, use password_verify($password, $hash)
		$password = password_hash($password, PASSWORD_DEFAULT);

		// Check if email and/or username is already used.
		$sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
		$result = $conn->query($sql);
		if($result->num_rows > 0)
		{
			// To-Do: If the email is already in use, redirect to email verification page without sending an email.
			// This will be done in an attempt to mitigate user enumeration.
			$valid_input = false;
		}

		$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
		$result = $conn->query($sql);
		if($result->num_rows > 0)
		{
			echo "<p>Username is taken.</p>";
			$valid_input = false;
		}

		// Perform email verification
		// To-Do: Create seperate page for verification and redirect to it here.
		// To-Do: Create database table for temporary unverified users with a column for the verification code.

		// Add account to database
		// Note: This code will need to be executed on the verification page after sucessful verification.
		if($valid_input)
		{
			$sql = "INSERT INTO users (email, username, password) VALUES ('$email', '$username', '$password')";

			if ($conn->query($sql) === TRUE) {
				echo "<p>New record created successfully</p>";
			} else {
				echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
			}
		}
		
		$conn->close();
	}
?>