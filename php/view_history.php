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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history-btn'])) {
    $clearStmt = $conn->prepare("DELETE FROM viewhistory WHERE UserID = ?");
    $clearStmt->bind_param("i", $userId);
    $clearStmt->execute();
    header("Location: view_history.php?cleared=1"); // Redirect prevents resubmission
    exit();
}

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
    <title>History</title>
    <link rel="stylesheet" href="../css/History.css">
    <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
    <?php include 'nav-bar.php' ?>
    <h1>&#128338;History</h1>
    <div class="container">
        <form method="post" onsubmit="return confirm('Are you sure you want to clear all your viewed history?');">
            <button type="submit" class="clear_history-btn" name="clear_history-btn">🗑️ Clear History</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <strong><?= htmlspecialchars($row['Title']) ?></strong><br>
                        <small>Viewed on <?= htmlspecialchars($row['ViewDate']) ?></small><br>
                        <a href="<?= htmlspecialchars($row['Source']) ?>" target="_blank" class="view-btn" >View Paper</a>
                        <button class="summarize-btn" data-title="<?= htmlspecialchars($row['Title']) ?>">Summarize</button>
                        <div class="summary-output"></div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No papers viewed yet.</p>
        <?php endif; ?>
    </div>
</body>
<script>
        document.querySelectorAll('.summarize-btn').forEach(button => {
            button.addEventListener('click', async () => {
                const title = button.getAttribute('data-title');
                const outputDiv = button.nextElementSibling;

                outputDiv.textContent = "Summarizing...";

                try {
                    const response = await fetch("chat.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ title })
                    });
                    const data = await response.json();
                    outputDiv.textContent = data.summary || "No summary available.";
                } catch (error) {
                    outputDiv.textContent = "Error summarizing.";
                }
            });
        });
</script>
</html>
