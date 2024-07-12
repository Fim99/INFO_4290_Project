<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery</title>
    <?php include '../bootstrap.html'?>
</head>

<body>
    <h1>Account Recovery</h1>
    <form name="account_recovery" method="post" action="">
        <div class="container-fluid pt-3">
            <div>
                <h3>An email will be sent to the email entered below</h3>
                <input type="text" name="email" id="email" placeholder="Email" required>
            </div>
            <div class='pt-2'>
                <button class="btn btn-primary" type="submit" name="password_reset_submit">Send Password Reset Link</button>
            </div>
        </div>
    </form>
    <?php
        if(isset($_GET["reset"])){
            if ($_GET["reset"] == "success"){
                echo "<p>Check your email for password reset code</p>";
            }
        }
    ?>
</body>
</html>