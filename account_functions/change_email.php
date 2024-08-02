<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	//Load Composer's autoloader
	require_once '../vendor/autoload.php';

	include_once '../nav.php';
	include_once '../account_functions/db_connection.php';

	const page_url = "http://localhost/INFO_4290_Project/account_functions/change_email.php";

	function error_message($string)
	{
		return "<div class='alert alert-danger mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
	}

	function success_message($string) 
	{
        return "<div class='alert alert-success mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
    }

	// Keep track of failed password guesses.
	if(!isset($_SESSION["password_attempts"]))
		$_SESSION["password_attempts"] = 0;
	const max_attempts = 5;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Email</title>
    <?php include_once '../bootstrap.html'?>
</head>


<!-- Verify password. -->
<?php if(!isset($_GET["selector"]) && !isset($_GET["validator"])) :

	require_once '../account_functions/check_loggin.php'; // Only check if logged in when initially requesting email change.

	if(isset($_POST["password"]))
	{
		$sql = "SELECT email, password FROM users WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $user_id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();
		$password_hash = $result["password"];
		$email = $result["email"];

		$valid_input = true;
		if($valid_input && !password_verify($_POST["password"], $password_hash))
		{
			$valid_input = false;
			$_SESSION['error_message'] = "Incorrect passsword.";
			$_SESSION["password_attempts"]++;
		}

		// Log out the user after too many failed attempts.
		if($_SESSION["password_attempts"] > max_attempts)
		{
			$_SESSION['error_message'] = "Too many failed password guesses.";

			$log_out = true;
			header('Refresh: 2; url=login.php');
		}

		if($valid_input)
		{
			$_SESSION["password_attempts"] = 0;
			$_SESSION['success_message'] = "An email has been sent to " . $email . " with instructions to change the email address of your account.";

			// Remove existing tokens
			$sql = "DELETE FROM email_change_requests WHERE user_id=?;";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $user_id);
			$stmt->execute();

			$selector = bin2hex(random_bytes(16));
        	$token = random_bytes(32);
			$hashed_token = password_hash($token, PASSWORD_DEFAULT);
			$sanitized_email = mysqli_real_escape_string($conn, $email);
			$expires = time() + (60*15); // 15 minutes
			$type = 0; // Verify ownership of old email address

			// Store email change request data in database.
			$sql = "INSERT INTO email_change_requests (user_id, email, selector, validator, type, expires) VALUES (?,?,?,?,?,?);";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("isssii", $user_id, $sanitized_email, $selector, $hashed_token, $type, $expires);
			$stmt->execute();

			// Send email with link.
			// For now, using non-functional emails.
			$url = page_url . "?selector=" . $selector . "&validator=" . bin2hex($token); // Temporary url
			
			$recieverEmail = $email;
			$senderEmail = 'nutritionappproject@gmail.com';
			$name = 'NutritionWebApp';
			$subject = "Email change request for " . $_SESSION["username"];
			$txt = "Click on this link to change the email address associated with your account: " . $url;
			
            // Creating a mail service with PHP Mailer
			$mail = new PHPMailer(true);
			$mail->isSMTP();
			$mail->SMTPAuth = true;
			$mail->Host = 'smtp.gmail.com'; 

			$mail->Username   = 'nutritionappproject@gmail.com';            //SMTP username
			$mail->Password   = 'iobp nwut dpeg kyus';                      //SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;             //Enable implicit TLS encryption
			$mail->Port = 587; 

			$mail->setFrom($senderEmail, $name);
			$mail->addAddress($recieverEmail);

			$mail->Subject = $subject;
			$mail->Body = $txt;

        	$mail->send();
		}
	}
?>
	<body>
		<div class="container d-flex align-items-center justify-content-center" style="padding-top:5vh">
			<div class="col-md-12" style="width:450px">
				<h1 class="text-center mb-4 display-6">Change Email</h1>
				<p class="text-center">Please re-enter your password to confirm your identity:</p>
				<div class="form-group row mb-4" > 
					<form name="confirm_password" style="display:inline-flex" method="post"> 
						<input type="password" name="password" id="password" class="form-control" placeholder="Password" autocomplete="off" required style="width:80%">
						<button class="btn btn-primary" type="submit" name="submit" style="width:19%; margin-left:1%">Submit</button>
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


	

<!-- Check validity of tokens if they are set. -->
<!-- These next two pages do not check if the user is logged in, in case the user opens the emails from another device. -->
<?php else :
	$selector = $_GET["selector"];
	$validator = $_GET["validator"];

	$sql = "SELECT * FROM email_change_requests WHERE (selector = ?) LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $selector);
	$stmt->execute();
	$results = $stmt->get_result();
	$result = $results->fetch_object();
	
	

	$valid_tokens = true;
	if ($results->num_rows == 0) 
	{
		$valid_tokens = false;
	}
	else
	{
		$new_email = $result->email;
		$hashed_validator = $result->validator;
		$expires = $result->expires;
		if(!password_verify(hex2bin($validator), $hashed_validator) || time() >= $expires)
		{
			$valid_tokens = false;
		}
	}

	if(!$valid_tokens)
	{
		// Go back to the initial page (or the login page if not logged in) if the token is invalid.
		header("Location: ../account_functions/change_email.php");
		$_SESSION["error_message"] = "Invalid email change token.";
		exit();
	}

	$token_type = $result->type; // Set the token type (old email or new email verification).

	$sql = "SELECT * FROM users WHERE (id = ?) LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $result->user_id);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_object();

	$user_id = $result->id;
	$username = $result->username;
	$email = $result->email;
	$password_hash = $result->password;
?>


	<!-- If user clicked on first confimration link (old email address). -->
	<?php if(isset($token_type) && $token_type == 0) :
		if(isset($_POST["submit"]))
		{
			$valid_input = true;
			$new_email = mysqli_real_escape_string($conn, $_POST["new_email"]);

			// Check if email is already in use.
			$sql = "SELECT * FROM users WHERE (email = ?) LIMIT 1";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("s", $new_email);
			$stmt->execute();
			if($stmt->get_result()->num_rows > 0)
			{
				$_SESSION["error_message"] = "The email you entered is already in use.";
				$valid_input = false;
			}

			// Check if password matches.
			if($valid_input && !password_verify($_POST["confirm_password"], $password_hash))
			{
				$_SESSION["error_message"] = "Incorrect password.";
				$_SESSION["password_attempts"]++;
				$valid_input = false;
			}

			// Log out the user after too many failed attempts.
			if($_SESSION["password_attempts"] > max_attempts)
			{
				$_SESSION['error_message'] = "Too many failed password guesses.";

				$log_out = true;
				header('Refresh: 2; url=login.php');
			}

			if($valid_input)
			{
				$_SESSION["password_attempts"] = 0;
				$_SESSION['success_message'] = "A verification email has been sent to " . $new_email . " for you to confirm the address change.";

				// Remove existing tokens
				$sql = "DELETE FROM email_change_requests WHERE user_id=?;";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $user_id);
				$stmt->execute();

				$selector = bin2hex(random_bytes(16));
				$token = random_bytes(32);
				$hashed_token = password_hash($token, PASSWORD_DEFAULT);
				$expires = time() + (60*15); // 15 minutes
				$type = 1; // Verify ownership of new email address

				// Store email change request data in database.
				$sql = "INSERT INTO email_change_requests (user_id, email, selector, validator, type, expires) VALUES (?,?,?,?,?,?);";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("isssii", $user_id, $new_email, $selector, $hashed_token, $type, $expires);
				$stmt->execute();

				// Send email with link.
				// For now, using non-functional emails.
				$url = page_url . "?selector=" . $selector . "&validator=" . bin2hex($token); // Temporary url

				$recieverEmail = $new_email;
				$senderEmail = 'nutritionappproject@gmail.com';
				$name = 'NutritionWebApp';
				$subject = "Email change confirmation for " . $username;
				$txt = "Click on this link to confirm the change of email address: " . $url;

				// Creating a mail service with PHP Mailer
				$mail = new PHPMailer(true);
				$mail->isSMTP();
				$mail->SMTPAuth = true;
				$mail->Host = 'smtp.gmail.com'; 

				$mail->Username   = 'nutritionappproject@gmail.com';            //SMTP username
				$mail->Password   = 'iobp nwut dpeg kyus';                      //SMTP password
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;             //Enable implicit TLS encryption
				$mail->Port = 587; 

				$mail->setFrom($senderEmail, $name);
				$mail->addAddress($recieverEmail);

				$mail->Subject = $subject;
				$mail->Body = $txt;

				$mail->send();
			}
		}
	?>
		<body>
			<div class="container d-flex align-items-center justify-content-center" style="padding-top:5vh">
				<div class="col-md-12" style="width:450px">
					<h1 class="text-center mb-4 display-6">Change Email</h1>
					<p class="text-center"><b>Username: </b><?php echo $username?></p>
					<p class="text-center">Please enter your new email address and current password:</p>
					<div class="form-group mb-4 text-center" > 
						<form name="change_new_email" style="" method="post"> 
							<input type="email" name="new_email" id="new_email" class="form-control" placeholder="Email" autocomplete="off" required style="margin-bottom:5px">
							<input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Password" autocomplete="off" required style="margin-bottom:10px">
							<button class="btn btn-primary" type="submit" name="submit" style="width:20%">Submit</button>
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



	<!-- If user clicked on the link in the second email (new email address). -->
	<?php elseif(isset($token_type) && $token_type == 1) :

		$sql = "UPDATE users SET email = ? WHERE id = '$user_id';";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $new_email);
		$stmt->execute();
		
		$_SESSION["success_message"] = "Successfully updated your email address."
	?>
		<body>
			<div class="container d-flex align-items-center justify-content-center" style="padding-top:5vh">
				<div class="col-md-12" style="width:450px">
					<h1 class="text-center mb-4 display-6">Change Email</h1>
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

	<?php endif; ?>


<!-- -->
<?php endif; 

	if (isset($log_out))
	{
		session_unset();
		session_destroy();
	}

?>

