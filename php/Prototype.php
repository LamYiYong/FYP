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
$output = null;
$papers = [];
$sort_by = $_POST['sort_by'] ?? '';
$sort_order = $_POST['sort_order'] ?? 'desc'; // Default sort descending

if (isset($_POST['search'])) {
    $search = escapeshellarg($_POST['search']);
    $command = "python search.py $search"; // Use python3 if needed
    $output = shell_exec($command);

    if ($output !== null) {
        $papers = json_decode($output, true);

        // Apply sorting
        if (!empty($papers) && is_array($papers)) {
            switch ($sort_by) {
                case 'year':
                    usort($papers, function($a, $b) use ($sort_order) {
                        return ($sort_order == 'asc') 
                            ? ($a['year'] ?? 0) <=> ($b['year'] ?? 0)
                            : ($b['year'] ?? 0) <=> ($a['year'] ?? 0);
                    });
                    break;
                case 'popularity':
                    usort($papers, function($a, $b) use ($sort_order) {
                        return ($sort_order == 'asc') 
                            ? ($a['num_citations'] ?? 0) <=> ($b['num_citations'] ?? 0)
                            : ($b['num_citations'] ?? 0) <=> ($a['num_citations'] ?? 0);
                    });
                    break;
                case 'region':
                    usort($papers, function($a, $b) use ($sort_order) {
                        return ($sort_order == 'asc') 
                            ? strcmp($a['venue'], $b['venue']) 
                            : strcmp($b['venue'], $a['venue']);
                    });
                    break;
                case 'author':
                    usort($papers, function($a, $b) use ($sort_order) {
                        $authorA = is_array($a['authors']) ? ($a['authors'][0] ?? '') : $a['authors'];
                        $authorB = is_array($b['authors']) ? ($b['authors'][0] ?? '') : $b['authors'];
                        return ($sort_order == 'asc') 
                            ? strcmp($authorA, $authorB) 
                            : strcmp($authorB, $authorA);
                    });
                    break;
                default:
                    // No sorting
                    break;
            }
        }
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
        <form method="post" class="input-section">
            <input type="text" name="search" placeholder="Enter research topic..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>" required>

            <!-- Sorting field -->
            <select name="sort_by" style="margin-left:10px; padding:0.5rem;">
                <option value="">Sort By</option>
                <option value="year" <?= ($sort_by == 'year' ? 'selected' : '') ?>>Publish Year</option>
                <option value="popularity" <?= ($sort_by == 'popularity' ? 'selected' : '') ?>>Popularity (Citations)</option>
                <option value="region" <?= ($sort_by == 'region' ? 'selected' : '') ?>>Region (Venue)</option>
                <option value="author" <?= ($sort_by == 'author' ? 'selected' : '') ?>>Prominent Author</option>
            </select>

            <!-- Sorting order field -->
            <select name="sort_order" style="margin-left:10px; padding:0.5rem;">
                <option value="desc" <?= ($sort_order == 'desc' ? 'selected' : '') ?>>↓ Descending</option>
                <option value="asc" <?= ($sort_order == 'asc' ? 'selected' : '') ?>>↑ Ascending</option>
            </select>

            <button type="submit">Search</button>
        </form>
        <div class="papers-list">
            <h2>Here are some papers you might be interested in:</h2>
            <div id="papers"></div>
        </div>
        <div class="logout">  
        </div>
        <div class="papers-list">
            <?php if (isset($_POST['search'])): ?>
                <?php if (isset($papers['error'])): ?>
                    <p style="color:red;">Error: <?= htmlspecialchars($papers['error']) ?></p>
                <?php elseif (empty($papers)): ?>
                    <p>No papers found.</p>
                <?php else: ?>
                    <h2>Results for "<?= htmlspecialchars($_POST['search']) ?>"
                    <?php if ($sort_by): ?>
                        (Sorted by <?= htmlspecialchars(ucwords(str_replace('_', ' ', $sort_by))) ?> <?= $sort_order == 'asc' ? '↑' : '↓' ?>)
                    <?php endif; ?>
                    </h2>

                    <?php foreach ($papers as $paper): ?>
                        <div class="paper-item">
                            <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
                            <div class="paper-details">
                                <strong>Authors:</strong> <?= htmlspecialchars(is_array($paper['authors']) ? implode(', ', $paper['authors']) : $paper['authors']) ?><br>
                                <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
                                <strong>Venue:</strong> <?= htmlspecialchars($paper['venue']) ?><br>
                                <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations'] ?? 0) ?><br><br>
                                <?php if (!empty($paper['abstract'])): ?>
                                    <strong>Abstract:</strong> <?= htmlspecialchars($paper['abstract']) ?><br><br>
                                <?php endif; ?>
                                <?php if (!empty($paper['url'])): ?>
                                    <a href="<?= htmlspecialchars($paper['url']) ?>" target="_blank">View Paper</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>