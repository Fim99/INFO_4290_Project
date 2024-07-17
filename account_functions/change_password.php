<?php
	session_start();

	// To-Do: Verify user is logged in and redirect if not.


	// Keep track of failed password guesses.
	if(!isset($_SESSION["password_change_attempts"]))
		$_SESSION["password_change_attempts"] = 0;
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

	include '../account_functions/db_connection.php';

	if(isset($_POST["submit"]))
	{
		$valid_input = true;

		// Check if "new password" and "confirm new password" are identical.
		if($_POST["confirm_new_password"] != $_POST["new_password"] && $valid_input)
		{
			$valid_input = false;
			error_message("'New Password' and 'Confirm New Password' inputs must match.");
			$_SESSION["password_change_attempts"]++;
		}

		// Check if old password input is identical to new password input.
		if($_POST["old_password"] == $_POST["new_password"] && $valid_input)
		{
			$valid_input = false;
			error_message("The new password cannot be identical to the current password.");
		}


		// To-Do: Verify new password meets minimum requirements.



		// To-Do: Compare hash of user-entered current password to the password hash in the database.
		// Keep track of failed password guesses here.
		$password_hash = password_hash($_POST["old_password"], PASSWORD_DEFAULT);


		// Log out the user after too many failed attempts.
		if($_SESSION["password_change_attempts"] > 5 && $valid_input)
		{
			$valid_input = false;
			error_message("Too many failed password guesses.");

			// To-Do: Handle log-out.
		}


		// To-Do: If there are no errors, update password in database with new hash.


		// To-Do: Redirect back to account information page after successful password change.
	}
?>
