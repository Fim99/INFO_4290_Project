<!-- Password change interface, accessed from account info page. Assumes user is logged in. -->

<?php
	session_start();
	include '../account_functions/check_loggin.php';
	include '../account_functions/db_connection.php';

	// Keep track of failed password guesses.
	if(!isset($_SESSION["password_change_attempts"]))
		$_SESSION["password_change_attempts"] = 0;
	const max_attempts = 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <?php include '../bootstrap.html'?>
</head>

<body>
	<div class="align-content-center mt-5" style="width:20em; margin:auto">
		<h1 class="text-center">Change Password</h1>
		<form name="change_password" method="post">
			<div class="mb-2">
				<input type="password" name="old_password" class="form-control" id="old_password" placeholder="Current Password" minlength="8" maxlength="72" required>
			</div>
			<div class="mb-2">
			</div>
				<input type="password" name="new_password" class="form-control" id="confirm_new_password" placeholder="New Password" minlength="8" maxlength="72" required>
			<div class="mb-2">
			</div>
				<input type="password" name="confirm_new_password" class="form-control" id="confirm_new_password" placeholder="Confirm New Password" minlength="8" maxlength="72" required>
			<div class="mt-2 mb-2 text-center">
				<button type="submit" name="submit" class="btn btn-primary">Submit</button>
			</div>
		</form>
	</div>
</body>

<?php
	function error_message($string)
	{
		echo "<p class='text-center mb-0' style='color:#e00000'>" . $string .  "</p>";
	}

	function success_message($string)
	{
		echo "<p class='text-center mb-0' style='color:#008000'>" . $string .  "</p>";
	}

	if(isset($_POST["submit"]))
	{
		$valid_input = true;

		// Check if "new password" and "confirm new password" are identical.
		if($_POST["confirm_new_password"] != $_POST["new_password"] && $valid_input)
		{
			$valid_input = false;
			error_message("'New Password' and 'Confirm New Password' inputs must match.");
		}

		// Check if old password input is identical to new password input.
		if($_POST["old_password"] == $_POST["new_password"] && $valid_input)
		{
			$valid_input = false;
			error_message("The new password cannot be identical to the current password.");
		}


		// To-Do: Verify new password meets minimum requirements.
		

		// Compare hash of user-entered current password to the password hash in the database.
		// Keep track of failed password guesses here.
		$result = $conn->query("SELECT * from users WHERE id = '$user_id'")->fetch_object();
		if($valid_input && !password_verify($_POST["old_password"], $result->password))
		{
			$valid_input = false;
			error_message("Incorrect password.");
			$_SESSION["password_change_attempts"]++;
			echo $_SESSION["password_change_attempts"];
		}


		// Log out the user after too many failed attempts.
		if($_SESSION["password_change_attempts"] > max_attempts)
		{
			error_message("Too many failed password guesses.");

			// Handle log-out.
			session_unset();
			session_destroy();
			
			header('Refresh: 2; url=login.php');
		}


		// If there are no errors, update password in database with new hash.
		if($valid_input)
		{
			$new_password_hash = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
			$conn->query("UPDATE users SET password = '$new_password_hash' WHERE id = '$user_id'");

			// To-Do: Send email informing user that their password has changed.


			// Redirect back to account information page after successful password change.
			success_message("Your password has been changed successfully.");
			header('Refresh: 2; url=account_information.php');
		}
	}
?>
