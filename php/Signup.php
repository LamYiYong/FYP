<?php
$conn = new mysqli("localhost", "root", "", "fyp");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "INSERT INTO users (Name, Email, Password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo '<script>alert("Register Successfully.")</script>';
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-Up Page</title>
    <link rel="stylesheet" href="../css/Signup.css">
</head>
<body>
    <div class="signup-container">
        <h1>Sign Up</h1>
        <form class="signup-form" action="Signup.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="abc@012" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="abc@gmail.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Abc@1234" required>
                <input type="checkbox" id="showPassword" name="showPassword">
                <label for="showPassword">Show Password</label>
            </div>
            <button type="submit" class="signup-btn">Sign Up</button>
            <p class="login-link">Already have an account? <a href="Login.php">Log In</a></p>
        </form>
    </div>
    <script src="../js/SignUp.js"></script>
</body>
</html>
