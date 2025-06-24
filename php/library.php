<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['UserID'];

$sql = "SELECT Title, PaperID, SavedAt FROM savedpapers WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/Prototype.css">
  <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
<h1>📚 My Bookmark</h1>
<div class="container">
 <form method="POST" action="delete_selected.php" onsubmit="return confirm('Delete selected papers?');">
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($row['PaperID']) ?>" />
                <strong><?= htmlspecialchars($row['Title']) ?></strong><br>
                <small>Saved on: <?= date('Y-m-d', strtotime($row['SavedAt'])) ?></small><br/>
                <a href="view_logger.php?paper_id=<?= urlencode($row['PaperID']) ?>&title=<?= urlencode($row['Title']) ?>" 
                    class="view-btn" target="_blank">View Paper </a> 
            </li>
        <?php endwhile; ?>
    </ul>
    <button type="submit" class="delete-btn">🗑️ Delete</button>
</form>
</div>
</body>
</html>
