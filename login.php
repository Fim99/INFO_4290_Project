<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include 'bootstrap.html'?>
</head>

<body>
    <h1>Login</h1>
    <form name="login" method="post" action="#">
        <div class="container-fluid pt-3">
            <div>
                <input type="text" name="username" id="username" placeholder="Username" required>
            </div>
            <div class="pt-2 pb-2">
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>
            <div class="d-inline">
                <button class="btn btn-primary" type="submit" name="submit">Log In</button>
                <a href=#>Forgot Password?</a>
            </div>
            <div class="pt-2">
                <a href="register.php"><button class="btn btn-secondary" type="button" name="register" id="register">Register Here</button></a>
            </div>
        </div>
    </form>
</body>
</html>

<?php
	$sql_servername = "localhost";
	$sql_username = "root";
	$sql_password = "";
	$sql_dbname = "nutritional_tracker";

	// Create connection
	$conn = new mysqli($sql_servername, $sql_username, $sql_password, $sql_dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	if(isset($_POST["submit"]))
	{
		$username = $_POST["username"];
		$password = $_POST["password"];

		// Protect against SQL injection attack
		$username = stripcslashes($username);  
        $password = stripcslashes($password);  
        $username = mysqli_real_escape_string($conn, $username);  
        $password = mysqli_real_escape_string($conn, $password);  

		$sql = "SELECT * FROM users where username = '$username'";
		$result = $conn->query($sql);
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

		$hashed_password = $row["password"];
		$count = mysqli_num_rows($result);  
		
		// Checking if inputs match database
		if($result->num_rows == 1)
		{
			echo "<h1>Username found</h1>";
			if(password_verify($password, $hashed_password)){
				echo '<h1>Password is correct</h1>';
			}
			else
				echo '<h1>Password is incorrect</h1>';
		}
		else
			echo "<h1>Username not found</h1>";

        $conn->close();
    }

?>
