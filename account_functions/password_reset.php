<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require '../vendor/autoload.php';   

    // Check if user clicked on send code button to access this page
    // Redirect users to index page if not
    if (isset($_POST["password_reset_submit"])){
        // Creating 2 tokens to avoid timing attacks
        $selector = bin2hex(random_bytes(16));
        $token = random_bytes(32);

        // URL that will be sent by email to user for creating a new password
        $url = "../account_functions/create_new_password.php?selector=$selector&$validator=".bin2hex($token);

        // Expire time for token in seconds
        $expire = date("U") + 900;  // 900 seconds = 15 minutes
       
        include_once '../account_functions/db_connection.php';

        $userEmail = $_POST["email"];

        // Delete any pre-existing tokens user may have
        $sql = "DELETE FROM password_reset WHERE pwdResetEmail=?;";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)){
            echo "Error in deleting token";
            exit();
        }
        else{
            mysqli_stmt_bind_param($stmt, "s", $userEmail);
            mysqli_stmt_execute($stmt);
        }

        // Add token details into password_reset table
        $sql = "INSERT INTO password_reset(pwdResetEmail, pwdResetSelector, pwdResetToken, pwdResetExpire) VALUES(
        ?,?,?,?);";
        
        $stmt = mysqli_stmt_init($conn);
        // Error checking
        if(!mysqli_stmt_prepare($stmt, $sql)){
            echo "Error in inserting token";
            exit();
        }
        else{
            // Hashing token to be entered in database
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt, "ssss", $userEmail, $selector, $hashedToken, $expire);
            mysqli_stmt_execute($stmt);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Configuring email inputs 
        $senderEmail = 'nutrition@mail.com';
        $to = $userEmail;
        $name = 'NutritionWebApp';
        $subject = "Password Reset Request for Nutrition App";
        $txt = "<p>A password reset has been requested on your account on Nutrition App. 
        The link to reset your password is below. If you did not make this request, you can ignore this email.</p>";
        $txt .= "<p>Password Reset Link: <a href>$url</a></p>";

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
        $mail->addAddress($to);

        $mail->Subject = $subject;
        $mail->Body = $txt;

        // Formatting for mail service
        $headers = "From: NutritionApp <nutritionappproject@gmail.com>\r\n";
        $headers .= "Reply-To: nutritionappproject@gmail.com\r\n";
        $headers .="Content-type: text/html\r\n";       // Use HTML in email

        $mail->send();

        // Send user back to forgot password form with success message
        header("Location: ../account_functions/forgot_password.php?reset=success");

    }

    else{
        header("Location: ../index.php");
    }
?>