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
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Home</a>
            <a class="navbar-brand" href="meal_functions/meal_records.php">Meal Records</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['username'])): ?>
                        <?php if (isset($_SESSION['current_meal_name'])): ?>
                            <li class="nav-item">
                                <span class="nav-link text-muted">Currently Selected Meal: <?php echo htmlspecialchars($_SESSION['current_meal_name']); ?></span>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <span class="nav-link text-dark"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <form class="d-inline" method="post">
                                <button type="submit" name="delete_session" class="btn btn-danger">Logout</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="nav-link text-dark">Guest</span>
                        </li>
                        <li class="nav-item">
                            <a href="account_functions/login.php" class="btn btn-primary">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>

</html>
