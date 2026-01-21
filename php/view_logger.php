<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

$paper_id = $_GET['paper_id'] ?? '';
$paper_title = $_GET['title'] ?? '';

if ($paper_id && $paper_title) {
    $stmt = $conn->prepare("INSERT INTO viewhistory (UserID, PaperID, PaperTitle, ViewedAt) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $_SESSION['UserID'], $paper_id, $paper_title);
    $stmt->execute();
    $stmt->close();

    // Redirect to actual paper
    header("Location: $paper_id");
    exit();
} else {
    echo "Invalid paper info.";
}
?>