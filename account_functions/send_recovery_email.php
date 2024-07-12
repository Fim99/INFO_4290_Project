<?php
    // This file uses nutritionappproject@gmail.com as an SMTP server to send emails
    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require '../vendor/autoload.php';

    // Email address that will recieve the email
    $recieverEmail = 'example@gmail.com';       // Enter email address that will recieve email

    // Configuring email inputs 
    $senderEmail = 'nutrition@mail.com';
    $name = 'Nutrition';
    $subject = 'testing nutrtion email';
    $message = 'sent';

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
    $mail->Body = $message;

    $mail->send();
    echo 'sent';
?>