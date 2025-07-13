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
  <title>Buyer Registration | Onyx</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    :root {
      --primary: #3a0ca3;
      --primary-light: #4361ee;
      --secondary: #f72585;
      --dark: #14213d;
      --light: #f8f9fa;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --danger: #ef233c;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background: #f5f7fa;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    }

    .container {
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      width: 100%;
      max-width: 420px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    h2 {
      margin-bottom: 24px;
      color: var(--dark);
      font-size: 28px;
      font-weight: 600;
    }

    .logo {
      color: var(--primary);
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .logo span {
      color: var(--secondary);
    }

    .form-group {
      position: relative;
      margin-bottom: 20px;
      text-align: left;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      color: var(--dark);
      font-weight: 500;
    }

    .input-field {
      width: 100%;
      padding: 14px 16px 14px 48px;
      border-radius: 8px;
      border: 1px solid var(--light-gray);
      font-size: 15px;
      transition: all 0.3s ease;
      background-color: var(--light);
    }

    .input-field:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
      outline: none;
    }

    .input-icon {
      position: absolute;
      left: 16px;
      top: 42px;
      color: var(--gray);
      font-size: 20px;
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      color: white;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
      box-shadow: 0 4px 12px rgba(58, 12, 163, 0.2);
    }

    button:hover {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      box-shadow: 0 6px 16px rgba(58, 12, 163, 0.3);
      transform: translateY(-2px);
    }

    .error {
      color: var(--danger);
      margin-bottom: 16px;
      padding: 12px;
      background-color: rgba(239, 35, 60, 0.1);
      border-radius: 6px;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .error i {
      margin-right: 8px;
    }

    .login-link {
      color: var(--gray);
      text-decoration: none;
      display: block;
      margin-top: 24px;
      font-size: 14px;
      transition: color 0.3s ease;
    }

    .login-link a {
      color: var(--primary);
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .login-link a:hover {
      color: var(--secondary);
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .container {
        padding: 30px 20px;
      }
      
      h2 {
        font-size: 24px;
      }
      
      .input-field {
        padding: 12px 14px 12px 42px;
      }
      
      .input-icon {
        font-size: 18px;
        top: 38px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">ONYX<span>.</span></div>
    <h2>Buyer Registration</h2>
    
    <?php if (!empty($error)): ?>
      <div class="error">
        <i class="material-icons">error</i>
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Username</label>
        <i class="material-icons input-icon">person</i>
        <input type="text" id="username" name="username" class="input-field" placeholder="Create your username" required autocomplete="username">
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <i class="material-icons input-icon">email</i>
        <input type="email" id="email" name="email" class="input-field" placeholder="Enter your email" required autocomplete="email">
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <i class="material-icons input-icon">lock</i>
        <input type="password" id="password" name="password" class="input-field" placeholder="Create a password" required autocomplete="new-password">
      </div>
      
      <button type="submit">
        <span>Create Buyer Account</span>
      </button>
    </form>
    
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>