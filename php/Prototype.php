<?php
session_start();

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Login.php");
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

function safeOutput($value) {
    if (is_array($value)) {
        return htmlspecialchars(implode(', ', $value));
    }
    return htmlspecialchars($value);
}

$results = [];

if (isset($_POST['query'])) {
    $query = escapeshellarg($_POST['query']);

    // Run the Python script
    shell_exec("python3 search.py $query");

    // Load the JSON results
    if (file_exists('results.json')) {
        $jsonData = file_get_contents('results.json');
        $results = json_decode($jsonData, true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Prototype.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
    <div class="container">
        <h1>AI-Driven Research Paper Suggestion System</h1>
        <div class="about">
            <p>Welcome! This system leverages AI to suggest research papers based on your interests.</p>
        </div>
        <div class="input-section">
            <form method="POST">
            <input type="text" name="query" placeholder="Enter your research interest (e.g., Computer Science)" required>
            <button type="submit">Search</button>
        </form>
        </div>
        <div class="papers-list">
            <h2>Here are some papers you might be interested in:</h2>
            <div id="papers"></div>   
            <?php if (!empty($results)): ?>
            <div class="papers-list">
                <?php foreach ($results as $paper): ?>
                    <div class="paper-item">
                        <div class="paper-title"><?= safeOutput($paper['title']) ?></div>
                        <div class="paper-details">
                            <?= safeOutput($paper['author']) ?> |
                            <?= safeOutput($paper['year']) ?>
                        </div>
                        <p><?= safeOutput($paper['abstract']) ?></p>
                        <?php if (!empty($paper['url']) && $paper['url'] !== "Unavailable"): ?>
                            <a href="<?= safeOutput($paper['url']) ?>" target="_blank">View Paper</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
        <div class="logout">
            
        </div>
    </div>
 
</body>

</html>