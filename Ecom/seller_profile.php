<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch seller info
$stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $password);
$stmt->fetch();
$stmt->close();

// Update seller info
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = $_POST["name"];
    $new_email = $_POST["email"];
    $new_password = $_POST["password"];

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $new_name, $new_email, $new_password, $user_id);
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $name = $new_name;
        $email = $new_email;
        $password = $new_password;
    } else {
        $message = "Failed to update profile.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Profile</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            margin:0px;
        }

        .profile-container {
            background: #fff;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        .msg {
            text-align: center;
            color: green;
            margin-bottom: 15px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input, button {
            padding: 10px;
            border-radius: 6px;
            font-size: 16px;
            border: 1px solid #ccc;
        }

        button {
            background-color:rgb(0, 0, 0);
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color:rgb(0, 0, 0);
        }
         .navbar {
      background-color: #222;
      color: white;
      padding: 1.5em;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      margin-left: 15px;
    }
    </style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
     <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
</head>
<body>
    <div class="navbar">
  <div><strong><a href="seller_dashboard.php">Seller Dashboard</a></strong></div>
  <div>
    <a href="add_product.php" class="logout"><span class="material-symbols-outlined">
add
</span></a>
    
    <a href="pending.php" class="logout"><span class="material-symbols-outlined">
pending_actions
</span></a>
    
     <a href="seller_profile.php" class="logout"><span class="material-icons">
account_circle
</span></a>
    <a href="logout.php" class="logout"><span class="material-symbols-outlined">
logout
</span></a>
  </div>
</div>
<br><br><br>
    <div class="profile-container">
        <h2>Seller Profile</h2>
        <?php if ($message): ?>
            <div class="msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <input type="text" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
