<?php
    session_start();
    include '../account_functions/db_connection.php';
	function error_message($string)
	{
		return "<div class='alert alert-danger mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
	}
	
    $valid_input = false;

    if (isset($_POST["submit"])) {
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $agree_terms = isset($_POST["agree_terms"]);

        $valid_input = true;

        // Check if the user agreed to the terms
        if (!$agree_terms && $valid_input) {
            $_SESSION['error_message'] = "You must agree to the terms and conditions.";
            $valid_input = false;
        }

        // Check if the passwords match.
        if ($password != $confirm_password && $valid_input) {
            $_SESSION['error_message'] = "Passwords do not match.";
            $valid_input = false;
        }

        // Limit the amount of possible characters for the username.
        if (preg_match("/[^A-z0-9_-]/", $username) && $valid_input) {
            $_SESSION['error_message'] = "Username contains invalid characters. Only alphanumeric characters, underscores, and hyphens are permitted.";
            $valid_input = false;
        }

        // Sanitize the email and username input.
        $sanitized_email = mysqli_real_escape_string($conn, $email);
        $sanitized_username = mysqli_real_escape_string($conn, $username);

        // Hash password for database storage
        $password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email is already used.
        $sql = "SELECT * FROM users WHERE email = '$sanitized_email' LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0 && $valid_input) {
            $_SESSION['error_message'] = "Email is already in use.";
            $valid_input = false;
        }

        // Check if username is already used.
        $sql = "SELECT * FROM users WHERE username = '$sanitized_username' LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0 && $valid_input) {
            $_SESSION['error_message'] = "Username is taken.";
            $valid_input = false;
        }

        // Perform email verification
        if ($valid_input) {
            $verification_code = rand(100000, 999999);

            $to = $email;
            $subject = "Account verification for " . $username;
            $txt = "Your verification code: " . $verification_code;
            $headers = "From: nutritional_tracker@test.com";
            mail($to, $subject, $txt, $headers);

            $expires = time() + (5 * 60); // 5 minutes until code expires

            $sql = "INSERT INTO unverified_users (email, username, password, code, expires) VALUES ('$sanitized_email', '$sanitized_username', '$password', '$verification_code', '$expires')";
            $conn->query($sql);

            $_SESSION["email"] = $sanitized_email;
            header("Location: ../account_functions/verify_account.php");
            exit();
        }

        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <?php include '../bootstrap.html'; ?>
    <link href="../custom.css" rel="stylesheet">
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh; padding-top: 5vh;">
        <div class="col-md-4">
            <h1 class="text-center mb-4">Account Registration</h1>
            <hr>
            <?php
                // Display error message if set
                if (isset($_SESSION['error_message'])) {
                    echo error_message($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                }
            ?>
            <form name="account_creation" method="post" action="">
                <div class="form-group mb-3">
                    <input type="email" name="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="Email Address" required>
                </div>
                <div class="form-group mb-3">
                    <input type="text" name="username" class="form-control" id="username" placeholder="Username" autocomplete="off" minlength="3" maxlength="50" required>
                </div>
                <div class="form-group mb-3">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" minlength="8" maxlength="72" required>
                </div>
                <div class="form-group mb-3">
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Confirm Password" minlength="8" maxlength="72" required>
                </div>
                <div class="form-group form-check mb-3">
                    <input type="checkbox" name="agree_terms" class="form-check-input" id="agree_terms" required>
                    <label class="form-check-label" for="agree_terms">
                        I agree that this site may store my personal data such as usernames, emails, paswords and meal data .
                    </label>
                </div>
                <div class="form-group text-center mb-3">
                    <button type="submit" name="submit" class="btn btn-primary w-100">Register</button>
                </div>
                <div class="text-center">
                    <p>Already have an account? <a href="login.php">Click here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
