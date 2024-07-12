<!--
Create new password form after user clicks link in email
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery</title>
    <?php include '../bootstrap.html'?>
</head>

<body>
    <h1>Password Reset</h1>
    <?php
        // Checking url parameters if tokens match
        $selector = $_GET["selector"];
        $validator = $_GET["validator"];
        if(empty($selector) || empty($validator)){
            echo "Could not validate tokens";
        }
        else{
            // Check if tokens are hexacimal format
            if(ctype_xdigit($selector) == true && ctype_xdigit($validator) == true){
                ?>
                <form action="../account_functions/password_reset.php" method="post">
                    <input type="hidden" name="selector" value="<?php echo $selector?>">
                    <input type="hidden" name="validator" value="<?php echo $validator?>">
                    <input type="password" name="pwd" placeholder="Enter your new password">
                    <input type="password" name="pwd_repeat" placeholder="Confirm new password">
                    <button type="submit" name="reset_password_submit">Reset Password</button>
                </form>
                <?php
            }
        }
    ?>
</body>
</html>