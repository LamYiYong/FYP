<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['UserID'];
$error = $success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 1️⃣ Basic validation
    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {

        // 2️⃣ Decide whether password is updated
        if (!empty($password)) {

            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters.";
            } elseif ($password !== $confirmPassword) {
                $error = "Passwords do not match.";
            } else {
                // ✅ Hash new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "UPDATE users SET Name=?, Email=?, Password=? WHERE UserID=?"
                );
                $stmt->bind_param("sssi", $name, $email, $hashedPassword, $userId);

                $stmt->execute()
                    ? $success = "Profile updated successfully."
                    : $error = "Update failed. Please try again.";
            }

        } else {
            // ✅ Update without touching password
            $stmt = $conn->prepare(
                "UPDATE users SET Name=?, Email=? WHERE UserID=?"
            );
            $stmt->bind_param("ssi", $name, $email, $userId);

            $stmt->execute()
                ? $success = "Profile updated successfully."
                : $error = "Update failed. Please try again.";
        }
    }
}

// Fetch current user info
$stmt = $conn->prepare("SELECT Name, Email FROM users WHERE UserID = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>


<!DOCTYPE html>
<html>

<head>
    <title>Update Profile</title>
    <link rel="stylesheet" href="../css/update_profile.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/Prototype.css">
</head>

<body>
    <?php include 'nav-bar.php' ?>
    <div class="form-container">
        <h2>&#128221;Update Profile</h2>
        <?php if ($success): ?><p class="success" style="color:green"><?= $success ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error" style="color:red"><?= $error ?></p><?php endif; ?>

        <form method="post">
            <p><label>Username:</label><br>
                <input type="text" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required>
            </p>

            <p><label>Email:</label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required>
            </p>

            <p><label>New Password (optional):</label><br>
                <input type="password" name="password">
            </p>

            <p><label>Confirm New Password:</label><br>
                <input type="password" name="confirm_password">
            </p>

            <div class="btn-container">
                <button type="submit" class="update-button">Update Profile</button>
                <a href="../php/profile.php" class="back-button">Back</a>
            </div>
        </form>
    </div>

</body>

</html>