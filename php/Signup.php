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
    <form class="signup-form">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" placeholder="Enter your username" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="Enter your email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="signup-btn">Sign Up</button>
      <p class="login-link">Already have an account? <a href="Login.html">Log In</a></p>
    </form>
  </div>
</body>
</html>

