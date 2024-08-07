<!--
Create new password form after user clicks link in email
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery</title>
    <?php include_once '../bootstrap.html'?>
</head>

<body>
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
                <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh; padding-top: 5vh;">
                    <div class="col-md-4">
                        <h1 class="text-center mb-4 display-6">Password Reset</h1>
                        <form action="../account_functions/password_reset.php" method="post">
                            <input type="hidden" name="selector" value="<?php echo $selector?>">
                            <div class="form-group mb-3">
                                <input type="hidden" name="validator" value="<?php echo $validator?>">
                            </div>
                            <div class="form-group mb-3">
                                <input type="password" name="pwd" class="form-control" placeholder="Enter your new password" required>
                            </div>
                            <div class="form-group mb-3">
                                <input type="password" name="pwd_repeat" class="form-control" placeholder="Confirm new password" required>
                            </div>
                            <div class="form-group mb-3">
                                <button type="submit" name="reset_password_submit" class="btn btn-primary w-100">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
        }
    ?>
</body>
</html>