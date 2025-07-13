<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $role = 2; // Buyer role

  // Check if username or email already exists
  $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    $error = "Username or email already exists";
  } else {
    // Insert user with role
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $password, $role);
    if ($stmt->execute()) {
      $_SESSION['user_id'] = $conn->insert_id;
      $_SESSION['role'] = $role;
      header("Location: buyer_home.php");
      exit;
    } else {
      $error = "Registration failed";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Buyer Registration</title>
  <style>
    body {
      background: #f5f7fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      width: 360px;
      text-align: center;
    }
    h2 {
      margin-bottom: 20px;
      color: #333;
    }
    form input[type="text"],
    form input[type="email"],
    form input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      margin: 10px 0 20px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 16px;
      box-sizing: border-box;
      transition: border-color 0.3s ease;
    }
    form input[type="text"]:focus,
    form input[type="email"]:focus,
    form input[type="password"]:focus {
      border-color: #28a745;
      outline: none;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #28a745;
      border: none;
      border-radius: 5px;
      font-size: 18px;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #1e7e34;
    }
    p.error {
      color: #e74c3c;
      margin-bottom: 15px;
    }
    a {
      color: #28a745;
      text-decoration: none;
      display: block;
      margin-top: 15px;
      font-size: 14px;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Buyer Registration</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required autocomplete="username" />
      <input type="email" name="email" placeholder="Email" required autocomplete="email" />
      <input type="password" name="password" placeholder="Password" required autocomplete="new-password" />
      <button type="submit">Register</button>
    </form>
    <a href="login_buyer.php">Already have an account? Login here</a>
  </div>
</body>
</html>
