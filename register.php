<?php
	session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <?php include 'bootstrap.html'?>
</head>

<body>
	<form name="account_creation" method="post" action="">
		<div style="width:25em" class="container-fluid col-6 mt-5 text-center">
			<h1 class="mb-3" style="font-size:2.4em">Account Registration</h1>
			<div class="d-flex mb-2">
				<input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Email Address" required>
			</div>
			<div class="mb-2">
				<input type="text" name="username" class="form-control" id="username" placeholder="Username" autocomplete="off" minlength="3" maxlength="50" required>
			</div>
			<div class="mb-2">
				<input type="password" name="password" class="form-control" id="password" placeholder="Password" minlength="8" maxlength="72" required>
			</div>
			<div class="mb-2">
				<input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="72" required>
			</div>
			<div class="mb-2" style="margin-left:auto">
				<button type="submit" name="submit" class="btn btn-primary w-50">Submit</button>
			</div>
			<p>Already have an account? <a href="login.php">Click here</a></p>
		</div>	
		
	</form>
</body>

<?php
	function error_message($string)
	{
		echo "<p class='text-center mb-0' style='color:#e00000'>" . $string .  "</p>";
	}

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

	$valid_input = false;

	if(isset($_POST["submit"]))
	{
		$email = $_POST["email"];
		$username = $_POST["username"];
		$password = $_POST["password"];
		$confirm_password = $_POST["confirm_password"];

		$valid_input = true;

		// Check if the passwords match.
		if ($password != $confirm_password && $valid_input)
		{
			error_message("Passwords do not match.");
			$valid_input = false;
		}

		// Limit the amount of possible characters for the username.
		if(preg_match("/[^A-z0-9_-]/", $username) && $valid_input)
		{
			error_message("Username contains invalid characters.");
			error_message("Only alphanumeric characters, underscores, and hyphens are permitted.");
			$valid_input = false;
		}

		// Sanitize the email and username input.
		// No need to sanatize the password, as it will be hashed.
		$sanitized_email = mysqli_real_escape_string($conn, $email);
		$sanitized_username = mysqli_real_escape_string($conn, $username);

		
		// Hash password for database storage
		// Note: To check if a password matches a hash, use password_verify($password, $hash)
		$password = password_hash($password, PASSWORD_DEFAULT);

		// Check if email is already used.
		$sql = "SELECT * FROM users WHERE email = '$sanitized_email' LIMIT 1";
		$result = $conn->query($sql);
		if($result->num_rows > 0 && $valid_input)
		{
			$_SESSION["email_in_use"] = true;
		}

		// Check if username is already used.
		$sql = "SELECT * FROM users WHERE username = '$sanitized_username' LIMIT 1";
		$result = $conn->query($sql);
		if($result->num_rows > 0 && $valid_input)
		{
			error_message("Username is taken.");
			$valid_input = false;
		}

		// To-Do: Enforce username and password requirements (e.g. minimum length, special characters, etc.)

		// Perform email verification
		// An email will be sent with the code.
		if($valid_input)
		{
			if(!isset($_SESSION["email_in_use"]))
			{
				$verification_code = rand(100000, 999999);

				// To-Do: Configure SMTP
				// For now, testing it locally with Papercut SMTP.
				$to = $email;
				$subject = "Account verification for " . $username;
				$txt = "Your verification code: " . $verification_code;
				$headers = "From: nutritional_tracker@test.com";
				mail($to,$subject,$txt,$headers);

				$expires = time() + (5 * 60); // 5 minutes until code expires

				// If the email is already used in the unverified_users table...
				$sql = "SELECT * FROM unverified_users WHERE email = '$sanitized_email' LIMIT 1";
				$result = $conn->query($sql);
				if($result->num_rows > 0)
				{
					// Update the existing entry.
					$sql = "UPDATE unverified_users SET username='$sanitized_username', password='$password', code='$verification_code', expires='$expires', attempts=0 WHERE email='$sanitized_email'";
				}
				else
				{
					// Add a new entry.
					$sql = "INSERT INTO unverified_users (email, username, password, code, expires) VALUES ('$sanitized_email', '$sanitized_username', '$password', '$verification_code', '$expires')";
				}
				
				$conn->query($sql);
			}
				
			$_SESSION["email"] = $sanitized_email;
			header("Location: verify_account.php");
		}
		
		$conn->close();
	}
?>