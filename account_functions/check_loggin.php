<?php
if (!isset($_SESSION["id"]))
{
    header("Location: ../account_functions/login.php"); // Redirect back to login.php if the user tried to go directly to this page.
    exit;
}
else
{
    $user_id = $_SESSION["id"];
}
?>