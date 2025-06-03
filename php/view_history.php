<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['UserID'])) {
    echo "<p style='padding:20px;'>Please log in to view your paper history.</p>";
    exit;
}

$userId = $_SESSION['UserID']; // ✅ moved before the clear_history logic

// Clear history logic
if (isset($_POST['clear_history'])) {
    $clear = $conn->prepare("DELETE FROM viewhistory WHERE UserID = ?");
    $clear->bind_param("i", $userId);
    $clear->execute();
    echo "<script>alert('Viewing history cleared.'); window.location.href='view_history.php';</script>";
    exit;
}

// Fetch history
$sql = "SELECT vh.ViewDate, p.Title, p.Authors, p.Source 
        FROM viewhistory vh
        JOIN paper p ON vh.PaperID = p.PaperID
        WHERE vh.UserID = ?
        ORDER BY vh.ViewDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>&#128338; View Paper History</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/Prototype.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
<div class="container">
    <h1>&#128338; History</h1>

    <!-- Clear History Button -->
    <form method="post" style="text-align: right; margin-bottom: 10px;">
        <button type="submit" name="clear_history" class="summarize-btn" style="background-color: crimson;">🗑️ Clear History</button>
    </form>

    <!-- Display History -->
    <?php if ($result->num_rows > 0): ?>
        <ul class="papers-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="paper-item">
                    <div class="paper-title"><?= htmlspecialchars($row['Title']) ?></div>
                    <div class="paper-details">
                        <strong>Authors:</strong> <?= htmlspecialchars($row['Authors']) ?><br>
                        <strong>Source:</strong> <?= htmlspecialchars($row['Source']) ?><br>
                        <strong>Date Viewed:</strong> <?= htmlspecialchars($row['ViewDate']) ?>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No viewing history available.</p>
    <?php endif; ?>
</div>
</body>
</html>
