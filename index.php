<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Search</title>
</head>

<body>
    <!-- Form for searching food -->
    <form action="" method="get">
        <input type="text" name="query" placeholder="Search for food..."
            value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>"><br>

        <label for="dataType">Data Type:</label>
        <select name="dataType" id="dataType">
            <option value="Branded" <?php if (isset($_GET['dataType']) && $_GET['dataType'] == 'Branded') echo 'selected'; ?>>Branded</option>
            <option value="Foundation" <?php if (isset($_GET['dataType']) && $_GET['dataType'] == 'Foundation') echo 'selected'; ?>>Foundation</option>
            <option value="Survey (FNDDS)" <?php if (isset($_GET['dataType']) && $_GET['dataType'] == 'Survey (FNDDS)') echo 'selected'; ?>>Survey (FNDDS)</option>
            <option value="SR Legacy" <?php if (isset($_GET['dataType']) && $_GET['dataType'] == 'SR Legacy') echo 'selected'; ?>>SR Legacy</option>
        </select><br>

        <label for="pageSize">Page Size:</label>
        <input type="number" name="pageSize" placeholder="E.g. 10" value="<?php echo htmlspecialchars($_GET['pageSize'] ?? '10'); ?>"><br>

        <label for="pageNumber">Page Number:</label>
        <input type="number" name="pageNumber" placeholder="E.g. 1" value="<?php echo htmlspecialchars($_GET['pageNumber'] ?? '1'); ?>"><br>

        <label for="sortBy">Sort By:</label>
        <select name="sortBy" id="sortBy">
            <option value="dataType.keyword" <?php if (isset($_GET['sortBy']) && $_GET['sortBy'] == 'dataType.keyword') echo 'selected'; ?>>Data Type</option>
            <option value="lowercaseDescription.keyword" <?php if (isset($_GET['sortBy']) && $_GET['sortBy'] == 'lowercaseDescription.keyword') echo 'selected'; ?>>Description</option>
            <option value="fdcId" <?php if (isset($_GET['sortBy']) && $_GET['sortBy'] == 'fdcId') echo 'selected'; ?>>FDC ID</option>
            <option value="publishedDate" <?php if (isset($_GET['sortBy']) && $_GET['sortBy'] == 'publishedDate') echo 'selected'; ?>>Published Date</option>
        </select><br>

        <label for="sortOrder">Sort Order:</label>
        <select name="sortOrder" id="sortOrder">
            <option value="asc" <?php if (isset($_GET['sortOrder']) && $_GET['sortOrder'] == 'asc') echo 'selected'; ?>>Ascending</option>
            <option value="desc" <?php if (isset($_GET['sortOrder']) && $_GET['sortOrder'] == 'desc') echo 'selected'; ?>>Descending</option>
        </select><br>

        <label for="brandOwner">Brand Owner:</label>
        <input type="text" name="brandOwner" placeholder="E.g. Pepsi"
            value="<?php echo htmlspecialchars($_GET['brandOwner'] ?? ''); ?>"><br>

        <button type="submit">Search</button>
    </form>

    <div id="results">
        <!-- Include search results from search.php -->
        <?php include 'search.php'; ?>
    </div>
</body>

</html>
