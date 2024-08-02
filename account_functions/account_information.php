<?php
	include_once '../nav.php';
	require_once '../account_functions/check_loggin.php';
	include_once '../account_functions/db_connection.php';

	$result = $conn->query("SELECT * from users WHERE id = '$user_id'")->fetch_object();
	$email = $result->email;
	$username = $result->username;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <?php include_once '../bootstrap.html'?>
</head>

<body>
	<div class="container d-flex align-items-center justify-content-center" style="padding-top:5vh">
		<div class="col-md-6" style="max-width: 400px">
			<h1 class="text-center display-6" style="">Account Settings</h1>
			
			<div class="form-group row mb-2">
				<label class="col-form-label" style="padding-bottom:0"><b>Email&nbsp;&nbsp;</b><a href="account_functions/change_email.php">Change</a></label>
					<input type="text" disabled class="form-control" value="<?php echo $email?>">
			</div>

			<div class="form-group row mb-4" >
				<label class="col-form-label" style="padding-bottom:0"><b>Username&nbsp;&nbsp;</b><a href="account_functions/change_username.php">Change</a></label>
					<input type="text" disabled class="form-control" value="<?php echo $username?>">
			</div>
			
			<div class="text-center">
				<button class="btn btn-secondary" onclick="location.href='account_functions/change_password.php'">Change Password</button>
			</div>
		</div>
	</div>	
</body>






