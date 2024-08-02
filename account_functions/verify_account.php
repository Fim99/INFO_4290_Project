<?php
	session_start();
	if(!isset($_SESSION["email"]))
	{
		header("Location: ../account_functions/register.php"); // Redirect back to register.php if the user tried to go directly to this page.
		exit;
	}
	else
	{
		$email = $_SESSION["email"];
	}

	if(isset($_SESSION["email_in_use"]) && !isset($_SESSION["attempts"]))
		$_SESSION["attempts"] = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <?php include_once '../bootstrap.html'?>
</head>

<body>
	<p>The code to complete verification has been sent your email at <?php echo $email; ?>.</p>
	<p>The code will expire in 5 minutes.</p>
	<form name="account_verification" method="post" action="">
		<div class="col-sm-3 mb-2">
			<label for="username" class="form-label">Enter Verification Code:</label>
			<input type="text" name="code" class="form-control" id="code" autocomplete="off" required>
		</div>
		<button type="submit" name="submit" class="btn btn-primary">Submit</button>
	</form>
</body>

<?php
	include_once '../account_functions/db_connection.php';

	$verified = false;
	$code_expired = false;
	$max_attempts_reached = false;
	if(isset($_POST["submit"]))
	{
		$sql = "SELECT * FROM unverified_users WHERE email = '$email' LIMIT 1";
		$results = $conn->query($sql);
		
		if($results->num_rows > 0) // If there is a matching entry in unverified_users
			$result = $results->fetch_object();
		else
			$code_expired = true;
			
		if(isset($result->expires) && time() < $result->expires) // If the current time is not past the code's expiration time.
			$correct_code = $result->code;
		else
			$code_expired = true;
		
		if(!$code_expired && $_POST["code"] == $correct_code) // If the correct code is entered by the user.
		{
			$username = $conn->real_escape_string($result->username);
			$password = $result->password;

			$sql = "INSERT INTO users (email, username, password) VALUES ('$email', '$username', '$password')";
			$conn->query($sql);
			$verified = true; // Account verified
			
			// Proceed to login page
			// Perhaps include an intermediary page, stating that the email is now verified.
			echo "<p>Your email has been succesfully verified!</p>";
			header('Refresh: 2; url=../account_functions/login.php');
		}
		else if ($code_expired && !isset($_SESSION["email_in_use"]))
		{
			echo "<p>The code has expired.</p>";
			header('Refresh: 2; url=../account_functions/register.php'); // Redirect back to the registration page after a few seconds.
		}
		else
		{
			if(!isset($_SESSION["email_in_use"]))
				$_SESSION["attempts"] = $result->attempts;
			
			if(++$_SESSION["attempts"] >= 5)
			{
				$max_attempts_reached = true;
				echo "<p>Maximum failed attempts reached. Code has expired.</p>";
				header('Refresh: 2; url=../account_functions/register.php');
			}
			else
			{
				echo "<p>Incorrect code.</p>";
				if(!isset($_SESSION["email_in_use"]))
					$conn->query("UPDATE unverified_users SET attempts=" . $_SESSION["attempts"] . " WHERE email='$email'");
			}
		}
	}

	if($verified || ($code_expired && !isset($_SESSION["email_in_use"])) || $max_attempts_reached)
	{
		$sql = "DELETE FROM unverified_users WHERE email = '$email'";
		$conn->query($sql);
		session_destroy();
		session_unset();
	}
?>


