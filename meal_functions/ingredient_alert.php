<?php
include '../nav.php';
include '../account_functions/check_loggin.php';
include '../account_functions/db_connection.php';

// Function to fetch ingredient alerts from the database
function getIngredientAlerts($conn, $user_id)
{
    $sql = "SELECT alerts FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0)
    {
        $row = $result->fetch_assoc();
        $alerts = json_decode($row['alerts'], true);
        $alerts = is_array($alerts) ? array_map('trim', $alerts) : [];

        // Return the list as is, since we are adding new items at the beginning
        return $alerts;
    }

    return [];
}

// Function to handle removing ingredients from alerts
function removeIngredientFromAlerts($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredient']))
    {
        // Check if user is logged in
        if (!isset($_SESSION['id']))
        {
            $_SESSION['error_message'] = "You must be logged in to remove ingredients from alerts.";
            return;
        }

        $ingredient = trim($_POST['ingredient']);
        $user_id = $_SESSION['id'];

        // Fetch the current alerts from the database
        $sql = "SELECT alerts FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $alerts = json_decode($row['alerts'], true);
            $alerts = is_array($alerts) ? array_map('trim', $alerts) : [];

            // Remove the ingredient from the alerts array
            if (in_array($ingredient, $alerts))
            {
                $alerts = array_diff($alerts, [$ingredient]);
                $alertsJson = json_encode($alerts);

                // Use a prepared statement to update the alerts
                $sqlUpdate = "UPDATE users SET alerts = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $alertsJson, $user_id);

                if ($stmtUpdate->execute())
                {
                    $_SESSION['success_message'] = "Ingredient removed from your alerts.";
                }
                else
                {
                    $_SESSION['error_message'] = "Error updating alerts: " . $stmtUpdate->error;
                }
            }
            else
            {
                $_SESSION['error_message'] = "Ingredient not found in your alerts.";
            }
        }
        else
        {
            $_SESSION['error_message'] = "User not found.";
        }
    }
}

// Function to handle adding a new ingredient to alerts
function addIngredientToAlerts($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_ingredient']))
    {
        // Check if user is logged in
        if (!isset($_SESSION['id']))
        {
            $_SESSION['error_message'] = "You must be logged in to add ingredients to alerts.";
            return;
        }

        $newIngredient = trim($_POST['new_ingredient']);
        if (empty($newIngredient))
        {
            $_SESSION['error_message'] = "The ingredient field cannot be empty.";
            return;
        }

        // Convert the ingredient to uppercase
        $newIngredient = strtoupper($newIngredient);
        $user_id = $_SESSION['id'];

        // Fetch the current alerts from the database
        $sql = "SELECT alerts FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $alerts = json_decode($row['alerts'], true);
            $alerts = is_array($alerts) ? array_map('trim', $alerts) : [];

            // Add the new ingredient to the alerts array if it's not already present
            if (!in_array($newIngredient, $alerts))
            {
                // Add to the beginning of the array
                array_unshift($alerts, $newIngredient);
                $alertsJson = json_encode($alerts);

                // Use a prepared statement to update the alerts
                $sqlUpdate = "UPDATE users SET alerts = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $alertsJson, $user_id);

                if ($stmtUpdate->execute())
                {
                    $_SESSION['success_message'] = "Ingredient added to your alerts.";
                }
                else
                {
                    $_SESSION['error_message'] = "Error updating alerts: " . $stmtUpdate->error;
                }
            }
            else
            {
                $_SESSION['error_message'] = "Ingredient is already in your alerts.";
            }
        }
        else
        {
            $_SESSION['error_message'] = "User not found.";
        }
    }
}

// Handle form submission for adding and removing ingredients from alerts
addIngredientToAlerts($conn);
removeIngredientFromAlerts($conn);

// Get the user ID from session
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingredient Alerts</title>
    <?php include '../bootstrap.html'; ?>
</head>

<body>
    <div class="container mt-4">
        <div class="col-md-10 mx-auto">
            <?php
            // Display success message if set
            if (isset($_SESSION['success_message']))
            {
                echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
                unset($_SESSION['success_message']);
            }

            // Display error message if set
            if (isset($_SESSION['error_message']))
            {
                echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
                unset($_SESSION['error_message']);
            }

            // Check if user is logged in
            if (!$user_id)
            {
                echo "<div class='alert alert-danger'>You must be logged in to view ingredient alerts.</div>";
                return;
            }

            // Form to add a new ingredient
            echo "<h2 class='display-5'>Ingredient Alert</h2>";
            echo "<hr>";
            echo "<form method='post'>";
            echo "<div class='row mb-3'>";
            echo "<div class='col-md-5'>";
            echo "<label for='new_ingredient' class='form-label'>Manually add an ingredient</label>";
            echo "<input type='text' id='new_ingredient' name='new_ingredient' class='form-control'>";
            echo "</div>";
            echo "<div class='col-md-4 d-flex align-items-end'>";
            echo "<button type='submit' class='btn btn-primary'>Add Ingredient</button>";
            echo "</div>";
            echo "</div>";
            echo "</form>";
            echo "<hr>";

            // Fetch and display ingredient alerts
            $alerts = getIngredientAlerts($conn, $user_id);

            if (empty($alerts))
            {
                echo "<p>No ingredients in your alert list.</p>";
            }
            else
            {
                echo "<h2 class='display-6'>Your Ingredient Alerts</h2>";
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th class='col-5'>Ingredient</th><th class='col-1 text-center'>Action</th></tr></thead>";
                echo "<tbody>";

                foreach ($alerts as $ingredient)
                {
                    echo "<tr class='ingredient-row'>";
                    echo "<td>" . htmlspecialchars($ingredient) . "</td>";
                    echo "<td class='text-center'>";
                    // Form to remove ingredient from alerts
                    echo "<form method='post' style='display:inline;'>";
                    echo "<input type='hidden' name='ingredient' value='" . htmlspecialchars($ingredient) . "'>";
                    echo "<button type='submit' class='btn btn-danger btn-sm' style='line-height: 15px;'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            }
            ?>

        </div>
    </div>
</body>

</html>
