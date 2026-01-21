<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['selected'])) {
    $userId = $_SESSION['UserID'];
    $selected = $_POST['selected'];

    $stmt = $conn->prepare("DELETE FROM savedpapers WHERE UserID = ? AND PaperID = ?");
    foreach ($selected as $paperId) {
        $stmt->bind_param("is", $userId, $paperId);
        $stmt->execute();
    }
}

// Redirect back to library page
header("Location: library.php");
exit();