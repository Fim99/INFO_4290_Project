<?php
session_start();

// Handle logout request
if (isset($_POST['delete_session']))
{
    session_destroy();
    header("Location: /INFO_4290_Project/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Tracker</title>
    <base href="/INFO_4290_Project/">
</head>

<body>
    <div class="navbar navbar-expand-lg navbar-light bg-light">
        <div>
            <a class="navbar-brand" href="index.php">Home</a>
            <a class="navbar-brand" href="meal_functions/meal_records.php">Meal Records</a>
        </div>
        <div class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['username'])): ?>
                <form class="form-inline logout-form" method="post">
                    <?php if (isset($_SESSION['current_meal_name'])): ?>
                        <span class="mr-3 text-muted">Currently Selected Meal: <?php echo htmlspecialchars($_SESSION['current_meal_name']); ?></span>
                    <?php endif; ?>
                    <span class="user-info mr-3"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <button type="submit" name="delete_session" class="btn btn-danger">Logout</button>
                </form>
            <?php else: ?>
                <span class="user-info mr-3 align-self-center">Guest</span>
                <a href="account_functions/login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
