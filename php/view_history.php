<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['UserID'];

// Fetch viewed papers for current user
$sql = "SELECT Title, PaperID AS Source, ViewDate 
        FROM viewhistory 
        WHERE UserID = ?
        ORDER BY ViewDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Viewed Research Paper History</title>
    <link rel="stylesheet" href="../css/Prototype.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'nav-bar.php' ?>
    
    <div class="container">
        <h2>Viewed Research Paper History</h2>

        <?php if ($result->num_rows > 0): ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li style="margin-bottom: 20px;">
                        <strong><?= htmlspecialchars($row['Title']) ?></strong><br>
                        <small>Viewed on <?= htmlspecialchars($row['ViewDate']) ?></small><br>
                        <a href="<?= htmlspecialchars($row['Source']) ?>" target="_blank">Open Paper</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No papers viewed yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
