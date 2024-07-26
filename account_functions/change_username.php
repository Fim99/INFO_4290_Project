<?php
	include '../nav.php';
	include '../account_functions/check_loggin.php';
	include '../account_functions/db_connection.php';

	function error_message($string)
	{
		return "<div class='alert alert-danger mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
	}

	function success_message($string) 
	{
        return "<div class='alert alert-success mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
    }

	if(isset($_POST["submit"]))
	{
		$valid_input = true;

		$username = $_POST["username"];

		if($_POST["username"] == $_SESSION["username"] && $valid_input)
		{
			$_SESSION['error_message'] = "The username you entered is identical to your current one.";
			$valid_input = false;
		}

		if(preg_match("/[^A-z0-9_-]/", $username) && $valid_input)
		{
			$_SESSION['error_message'] = "Username contains invalid characters. Only alphanumeric characters, underscores, and hyphens are permitted.";
			$valid_input = false;
		}

		$new_username = mysqli_real_escape_string($conn, $username);
		$result = $conn->query("SELECT * FROM users WHERE username = '$new_username' LIMIT 1");
		if ($result->num_rows > 0 && $valid_input)
		{
			$_SESSION['error_message'] = "The username you entered is already taken.";
			$valid_input = false;
		}

		if($valid_input)
		{
			$_SESSION["username"] = $new_username;
			$conn->query("UPDATE users SET username = '$new_username' WHERE id = $user_id");
			$_SESSION['success_message'] = "Successfully updated your username. Redirecting...";
			header('Refresh: 2; url=account_information.php');
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <?php include '../bootstrap.html'?>
	<link href="../custom.css" rel="stylesheet">
</head>

<body>
	<div class="container d-flex align-items-center justify-content-center" style="padding-top:5vh">
		<div class="col-md-12" style="width:450px">
			<h1 class="text-center mb-4 display-6">Change Username</h1>
				<p>Current Username: <?php echo $_SESSION["username"] ?></p>
			<div class="form-group row mb-4" > 
				<form name="change_username" style="display:inline-flex" method="post"> 
					<input type="text" name="username" id="username" class="form-control" placeholder="New Username" autocomplete="off" minlength="3" maxlength="50" required style="width:80%">
					<button class="btn btn-primary" type="submit" name="submit" style="width:19%; margin-left:1%">Update</button>
				</form>
			</div>
			<?php
                // Display error message if set
                if (isset($_SESSION['error_message'])) 
				{
                    echo error_message($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                }
				// Display success message if set
				else if (isset($_SESSION['success_message'])) 
				{
                    echo success_message($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                }
            ?>
		</div>
	</div>	
</body>

<?php

?>
