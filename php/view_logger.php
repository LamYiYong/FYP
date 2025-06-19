<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['UserID'])) {
    die("Not logged in.");
}

$userId = $_SESSION['UserID'];

$paperId = $_GET['paper_id'] ?? null;
$title = $_GET['title'] ?? 'Untitled';

if ($paperId) {
    $viewDate = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO viewhistory (UserID, PaperID, Title, ViewDate) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $paperId, $title, $viewDate);
    $stmt->execute();

    header("Location: " . $paperId);
    exit();
} else {
    echo "Invalid paper ID.";
}
?>
