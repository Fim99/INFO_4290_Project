<?php
// This file checks the form from create_new_password.php

if(isset($_POST["reset_password_submit"])){
    $selector = $_POST["selector"];
    $validator = $_POST["validator"];
    $password = $_POST["pwd"];
    $passwordRepeat = $_POST["pwd_repeat"];

    // Check password fields
    if(empty($password) || empty($passwordRepeat)){
        header("Location ../account_functions/create_new_password.php?newpwd=empty");    // tokens not included yet
        exit();
    }
    else if($password != $passwordRepeat){
        header("Location ../account_functions/create_new_password.php?newpwd=pwddonotmatch");  
        exit();
    }

    // Check expiry date for token
    $currentDate = date("U");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM password_reset WHERE pwdResetSelector=? AND pwdResetExpire >=?";
    $stmt = mysqli_stmt_init($conn);
    if(!mysqli_stmt_prepare($stmt, $sql)){
        echo "Error in selecting pwd from database";
        exit();
    }
    else{
        mysqli_stmt_bind_param($stmt, "s", $selector, $currentDate); // Need to double check parameters
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        // if password reset request was not found
        if(!$row = mysqli_fetch_assoc($result)){
            echo "Please resubmit your reset request";
            exit();
        }
        else {
            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row["pwdResetToken"]);    // matching tokens from link and database

            if($tokenCheck === false){
                echo "Please resubmit your reset request";
                exit();
            }
            else if ($tokenCheck === true){
                $tokenEmail = $row["pwdResetEmail"];

                $sql = "SELECT * FROM users WHERE email=?;";
                $stmt = mysqli_stmt_init($conn);
                if(!mysqli_stmt_prepare($stmt, $sql)){
                    echo "Error in selecting email from user table";
                    exit();
                }
                else{
                    mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                    mysqli_stmt_execute($stmt);

                    $result = mysqli_stmt_get_result($stmt);
                    if(!$row = mysqli_fetch_assoc($result)){
                        echo "Error with finding email in database";
                        exit();
                    }
                    // Updating password
                    else {
                        $sql = "UPDATE users SET password=? WHERE email=?";
                        if(!mysqli_stmt_prepare($stmt, $sql)){
                            echo "Error in updating password";
                            exit();
                        }
                        else{
                            $newPwdHash = password_hash($password, PASSWORD_DEFAULT);
                            mysqli_stmt_bind_param($stmt, "ss", $newPwdHash, $tokenEmail);
                            mysqli_stmt_execute($stmt);

                            // Delete token after updating
                            $sql = "DELETE FROM password_reset WHERE pwdResetEmail=?";
                            $stmt = mysqli_stmt_init($conn);
                            if(!mysqli_stmt_prepare($stmt, $sql)){
                                echo "Error in selecting email from reset table";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($stmt, "s", $tokenEmail);
                                mysqli_stmt_execute($stmt);
                                header("Location: ../account_functions/login.php?newpwd=passwordupdated");
                            }
                        }
                    }
                }
            }
        }
    }

}
else{
    header("Location: ../index.php");
}