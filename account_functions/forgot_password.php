<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery</title>
    <?php include_once '../bootstrap.html'; ?>
    <link href="../custom.css" rel="stylesheet">
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh; padding-top: 5vh;">
        <div class="col-md-4">
            <h1 class="text-center mb-4 display-6">Account Recovery</h1>
            <hr>
            <?php
            // Display success message if applicable
            if (isset($_GET["reset"]) && $_GET["reset"] == "success")
            {
                echo "Check your email for the password reset code.";
            }
            ?>
            <form name="account_recovery" method="post" action="password_reset.php">
                <div class="form-group mb-3">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-group text-center mb-3">
                    <button class="btn btn-primary w-100" type="submit" name="password_reset_submit">Send Password Reset
                        Link</button>
                </div>
                <div class="text-center">
                    <p>Remember your password? <a href="login.php">Log In</a></p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>