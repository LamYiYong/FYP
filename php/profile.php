<?php
// Assuming you have a session started and the user is logged in
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change this to your DB username
$password = ""; // Change this to your DB password
$dbname = "fyp"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$userID = $_SESSION['UserID']; // Assuming userID is stored in the session after login
$sql = "SELECT Name, Email FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/Prototype.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
    <div class="container">
        <h1>Profile</h1>
        <div class="profile-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['Name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
            <a href="update_profile.php" class="update-button">Update Profile</a>
        </div>
    </div>
</body>
</html>
