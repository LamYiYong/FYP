<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['Password']) {
            $_SESSION['UserID'] = $user['UserID'];
            header("Location: Prototype.php");
            exit();
        } else {
            echo '<script>alert("Incorrect password.")</script>';
        }
    } else {
        echo '<script>alert("No user found with this email.")</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../css/Login.css">
</head>

<body>
    <div class="login-container">
        <form class="login-form" action="Login.php" method="POST">
            <img src="../css/image/icon.png" alt="">
            <h1>Welcome</h1>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="abc@gmail.com" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Abc@1234" required>
            </div>
            <input type="checkbox" id="showPassword"> Show Password
            <button type="submit" class="login-button">Login</button>
            <p class="signup-link">
                Don't have an account? <a href="Signup.php">Sign up</a>
            </p>
        </form>
    </div>
    <script src="../js/Login.js"></script>
</body>
</html>