<?php
if (!isset($_SESSION["id"]))
{
    header("Location: ../login.php"); // Redirect back to login.php if the user tried to go directly to this page.
    exit;
}
else
{
    $id = $_SESSION["id"];
}
?>