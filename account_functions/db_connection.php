<?php
// Database connection details
$sql_servername = "localhost";
$sql_username = "root";
$sql_password = "";
$sql_dbname = "nutritional_tracker";

$conn = new mysqli($sql_servername, $sql_username, $sql_password, $sql_dbname);
if ($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}
?>