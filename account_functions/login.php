<?php
    session_start();
    include '../account_functions/db_connection.php';

    function error_message($string) {
        return "<div class='alert alert-danger mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
    }

    function success_message($string) {
        return "<div class='alert alert-success mt-3 text-center'>" . htmlspecialchars($string, ENT_QUOTES, 'UTF-8') . "</div>";
    }

    if (isset($_POST["submit"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Protect against SQL injection attack
        $username = stripcslashes($username);  
        $password = stripcslashes($password);  
        $username = mysqli_real_escape_string($conn, $username);  
        $password = mysqli_real_escape_string($conn, $password);  

        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        
        if ($result->num_rows == 1) {
            $hashed_password = $row["password"];
            if (password_verify($password, $hashed_password)) {
                $_SESSION['success_message'] = "Login successful. Redirecting...";
                include 'after_login.php';  // Include file for initializing session
                $_SESSION['redirect'] = true; // Set a session variable to indicate redirection
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Password is incorrect.";
            }
        } else {
            $_SESSION['error_message'] = "Username not found.";
        }

        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include '../bootstrap.html'; ?>
    <link href="../custom.css" rel="stylesheet">
    <?php
        // If redirection is set, add a meta refresh tag to the head
        if (isset($_SESSION['redirect']) && $_SESSION['redirect']) {
            echo '<meta http-equiv="refresh" content="2;url=../index.php">';
            unset($_SESSION['redirect']);
        }
    ?>
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh; padding-top: 5vh;">
        <div class="col-md-4">
            <h1 class="text-center mb-4 display-6">Login</h1>
            <hr>
            <?php
                // Display success message if set
                if (isset($_SESSION['success_message'])) {
                    echo success_message($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                }

                // Display error message if set
                if (isset($_SESSION['error_message'])) {
                    echo error_message($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                }

                // Display password reset message if applicable
                if (isset($_GET["newpwd"]) && $_GET["newpwd"] == "passwordupdated") {
                    echo success_message("Your password was successfully changed.");
                }
            ?>
            <form name="login" method="post" action="">
                <div class="form-group mb-3">
                    <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="form-group mb-3">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="form-group text-center mb-3">
                    <button class="btn btn-primary w-100" type="submit" name="submit">Log In</button>
                </div>
                <div class="text-center mb-3">
                    <a href="../account_functions/register.php" class="btn btn-secondary w-100" role="button" name="register" id="register">Register Here</a>
                </div>
                <div class="text-center">
                    <p>Forgot Password? <a href="forgot_password.php">Click here</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
