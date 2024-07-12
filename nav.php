<!-- nav.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Tracker</title>
    <style>
        /* Basic styles for the navigation bar */
        .navbar 
        {
            background-color: #f8f9fa;
            padding: 10px;
        }

        .navbar a 
        {
            margin: 0 10px;
            text-decoration: none;
            color: #000;
        }

        .navbar a:hover 
        {
            text-decoration: underline;
        }
    </style>
    <base href="/INFO_4290_Project/">
</head>

<body>
    <div class="navbar navbar-expand-lg navbar-light bg-light">
        <a href="index.php">Home</a>
        <a href="account_functions/login.php">Login</a>
        <a href="meal_functions/meal_records.php">Meal Records</a>
    </div>
</body>

</html>