<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION['UserID'];
    $paperUrl = $_POST['paper_id'];
    $paperTitle = $_POST['title'] ?? '';

    // Check if already exists
    $stmt = $conn->prepare("INSERT IGNORE INTO savedpapers (UserID, PaperID, Title) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $paperUrl, $paperTitle);
    $stmt->execute();
}

header("Location: Prototype.php");
exit();
