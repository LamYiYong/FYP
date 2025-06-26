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
  <link rel="stylesheet" href="../css/bookmark.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
<h1>📚 My Bookmark</h1>
<div class="container">
    <!-- Sort Button -->
<!-- Sorting Buttons -->
 <div class="btn-container">
<button type="button" onclick="sortByDate('desc')" class="newest-date-btn">Sort by Date (Newest First)</button>
<button type="button" onclick="sortByDate('asc')" class="oldest-date-btn">Sort by Date (Oldest First)</button>
 </div>

<form method="POST" action="delete_selected.php" onsubmit="return confirm('Delete selected papers?');">
    <ul id="paper-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <li data-date="<?= htmlspecialchars($row['SavedAt']) ?>">
                <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($row['PaperID']) ?>" />
                <strong><?= htmlspecialchars($row['Title']) ?></strong><br>
                <small>Saved on: <?= date('Y-m-d', strtotime($row['SavedAt'])) ?></small>
                <br/>
                
                <a href="view_logger.php?paper_id=<?= urlencode($row['PaperID']) ?>&title=<?= urlencode($row['Title']) ?>" 
                    class="bookmark-link" target="_blank">
                    <button class="view-btn-bookmark" type="button">View Paper</button>
                </a>                
            </li>
        <?php endwhile; ?>
    </ul>
    <button type="submit" class="delete-btn">🗑️ Delete</button>
</form>
</div>
</body>
</html>


<script>
function sortByDate(order) {
    const list = document.getElementById("paper-list");
    const items = Array.from(list.querySelectorAll("li"));

    items.sort((a, b) => {
        const dateA = new Date(a.dataset.date);
        const dateB = new Date(b.dataset.date);

        if (order === 'asc') {
            return dateA - dateB; // Oldest first
        } else {
            return dateB - dateA; // Newest first
        }
    });

    // Re-append sorted items
    list.innerHTML = '';
    items.forEach(item => list.appendChild(item));
}
</script>

