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
    <base href="/INFO_4290_Project/">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="index.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="meal_functions/meal_records.php">Meal Records</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="meal_functions/ingredient_alert.php">Ingredient Alert</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="faq.php">FAQ / Support</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['username'])) : ?>
                        <?php if (isset($_SESSION['current_meal_name'])) : ?>
                            <li class="nav-item">
                                <span class="nav-link text-light">Currently Selected Meal:
                                    <?php echo htmlspecialchars($_SESSION['current_meal_name']); ?></span>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <span
                                class="nav-link text-light fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="account_functions/account_information.php">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 30"
                                    fill="white">
                                    <path
                                        d="M24 13.616v-3.232c-1.651-.587-2.694-.752-3.219-2.019v-.001c-.527-1.271.1-2.134.847-3.707l-2.285-2.285c-1.561.742-2.433 1.375-3.707.847h-.001c-1.269-.526-1.435-1.576-2.019-3.219h-3.232c-.582 1.635-.749 2.692-2.019 3.219h-.001c-1.271.528-2.132-.098-3.707-.847l-2.285 2.285c.745 1.568 1.375 2.434.847 3.707-.527 1.271-1.584 1.438-3.219 2.02v3.232c1.632.58 2.692.749 3.219 2.019.53 1.282-.114 2.166-.847 3.707l2.285 2.286c1.562-.743 2.434-1.375 3.707-.847h.001c1.27.526 1.436 1.579 2.019 3.219h3.232c.582-1.636.75-2.69 2.027-3.222h.001c1.262-.524 2.12.101 3.698.851l2.285-2.286c-.744-1.563-1.375-2.433-.848-3.706.527-1.271 1.588-1.44 3.221-2.021zm-12 2.384c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4z" />
                                </svg>

                            </a>
                        </li>
                        <li class="nav-item">
                            <form class="d-inline" method="post">
                                <button type="submit" name="delete_session" class="btn btn-danger">Logout</button>
                            </form>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <span class="nav-link text-light fw-bold">Guest</span>
                        </li>
                        <li class="nav-item">
                            <a href="account_functions/login.php" class="btn btn-success">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>

</html>