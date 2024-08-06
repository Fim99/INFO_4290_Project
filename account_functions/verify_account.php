<?php
	session_start();
	include_once '../account_functions/db_connection.php';

	if(!isset($_SESSION["email"]))
	{
		header("Location: ../account_functions/register.php"); // Redirect back to register.php if the user tried to go directly to this page.
		exit;
	}
	else
	{
		$email = $_SESSION["email"];
	}

	
	if(!isset($_SESSION["attempts"]))
		$_SESSION["attempts"] = 0;

	function error_message($string)
	{
		return "<div class='alert alert-danger mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
	}

	function success_message($string) 
	{
		return "<div class='alert alert-success mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
	}

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
			$_SESSION['success_message'] = "Your email has been succesfully verified!";
			
			$redirect = true;
			header('Refresh: 2; url=../account_functions/login.php');
		}
		else if ($code_expired && !isset($_SESSION["email_in_use"]))
		{
			$_SESSION['error_message'] = "The code has expired.";
			$redirect = true;
			header('Refresh: 2; url=../account_functions/register.php'); // Redirect back to the registration page after a few seconds.
		}
		else
		{
			$_SESSION["attempts"] = $result->attempts;
			
			if(++$_SESSION["attempts"] >= 5)
			{
				$max_attempts_reached = true;
				$_SESSION['error_message'] = "Maximum failed attempts reached. Code has expired.";
				$redirect = true;
				header('Refresh: 2; url=../account_functions/register.php');
			}
			else
			{
				$_SESSION['error_message'] = "Incorrect code.";
				$conn->query("UPDATE unverified_users SET attempts=" . $_SESSION["attempts"] . " WHERE email='$email'");
			}
		}
	}

	if($verified || $code_expired || $max_attempts_reached)
	{
		$sql = "DELETE FROM unverified_users WHERE email = '$email'";
		$conn->query($sql);
	}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <?php include_once '../bootstrap.html'?>
	<link href="../custom.css" rel="stylesheet">
</head>

<body>
	<div class="container d-flex align-items-center justify-content-center" style="padding-top:10vh">
		<div class="col-md-12" style="width:450px">
			<h1 class="text-center mb-4 display-6">Verify Account</h1>
			<p class="text-center">The code to complete verification has been sent your email at <b><?php echo $email; ?></b>.</p>
			<p class="text-center">The code will expire in 5 minutes.</p>
			<div>
				<form name="account_verification" method="post" action="" style="width:60%; margin-left:20%; display:inline-flex">
					<input type="text" name="code" class="form-control" id="code" autocomplete="off" placeholder="Verification Code" minlength="6" maxlength="6" required style="width:68%">
					<button type="submit" name="submit" class="btn btn-primary" style="width:30%; margin-left:2%">Submit</button>
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
	if(isset($redirect))
	{
		session_unset();
		session_destroy();
	}
?>


