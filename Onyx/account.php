<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user info
$stmt = $conn->prepare("SELECT username, email, password, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $password, $created_at);
$stmt->fetch();
$stmt->close();

// Format registration date
$registration_date = date("F j, Y", strtotime($created_at));

// Update user info
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = $_POST["name"];
    $new_email = $_POST["email"];

    if (!empty($_POST["password"])) {
        $new_password = $_POST["password"]; // ❌ No hashing here
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $new_name, $new_email, $new_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $name = $new_name;
        $email = $new_email;
    } else {
        $message = "Failed to update profile.";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
      --primary: #3a0ca3;
      --primary-light: #4361ee;
      --secondary: #f72585;
      --dark: #14213d;
      --light: #f8f9fa;
      --gray: #6c757d;
      --success: #4cc9f0;
      --warning: #f8961e;
      --danger: #ef233c;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f5f7;
      color: #333;
      line-height: 1.6;
    }

    .navbar {
      background-color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
      text-decoration: none;
      display: flex;
      align-items: center;
    }

    .logo span {
      color: var(--secondary);
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 1.5rem;
    }

    .nav-links a {
      color: var(--dark);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      position: relative;
    }

    .nav-links a:hover {
      color: var(--primary);
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      background: var(--primary);
      bottom: -4px;
      left: 0;
      transition: width 0.3s ease;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    .location-badge {
      background: #f0f2ff;
      padding: 0.5rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }

    .location-badge span {
      color: var(--primary);
      font-size: 1rem;
    }
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --danger-color: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            align-self: start;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 5px solid var(--light-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-color);
        }
        
        .profile-name {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .profile-email {
            color: #6c757d;
            margin-bottom: 1.5rem;
            word-break: break-all;
        }
        
        .profile-meta {
            text-align: left;
            margin-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            padding-top: 1.5rem;
        }
        
        .profile-meta-item {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
        }
        
        .profile-meta-item i {
            margin-right: 0.5rem;
            color: var(--accent-color);
            width: 20px;
            text-align: center;
        }
        
        .profile-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1rem;
        }
        
        .profile-title {
            font-size: 1.8rem;
            color: var(--dark-color);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: rgba(75, 181, 67, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(75, 181, 67, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(72, 149, 239, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 5px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    
<div class="navbar">
  <a href="buyer_home.php" class="logo">ONYX<span>.</span></a>
  <div class="nav-links">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div id="google_translate_element"></div>

    <!-- Google Translate script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
        }
    </script>

    <!-- Load Google Translate API -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
      <div class="location-badge">
        <span class="material-symbols-outlined">location_on</span>
        <span id="user-city">Detecting city...</span>
      </div>
      <a href="cart.php"><span class="material-symbols-outlined">
shopping_cart
</span>
        <?php 
          $count = 0;
          if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
              $count += $item['quantity'];
            }
          }
          if ($count > 0) {
            echo "($count)";
          }
        ?>
      </a>
      <a href="account.php">My Account</a>
      <a href="view_orders.php">Orders</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <div class="location-badge">
        <span class="material-symbols-outlined">location_on</span>
        <span id="user-city">Detecting city...</span>
      </div>
      <a href="sellerreg.php">Become a Seller</a>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
</div>
    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($name); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
            
            <div class="profile-meta">
                <div class="profile-meta-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Member since <?php echo $registration_date; ?></span>
                </div>
                <div class="profile-meta-item">
                    <i class="fas fa-key"></i>
                    <span>Last updated: <?php echo date("F j, Y"); ?></span>
                </div>
            </div>
        </aside>
        
        <main class="profile-content">
            <div class="profile-header">
                <h1 class="profile-title">Profile Settings</h1>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name" class="form-label">Username</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="password" class="form-control">
                    <i class="fas fa-eye password-toggle-icon" id="togglePassword"></i>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        
        // Show success message for 5 seconds then fade out
        const alertMessage = document.querySelector('.alert');
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.opacity = '0';
                setTimeout(() => {
                    alertMessage.style.display = 'none';
                }, 500);
            }, 5000);
        }
    </script>
    <script>
window.addEventListener("DOMContentLoaded", () => {
  const cityElement = document.getElementById("user-city");

  function displayCity(cityName) {
    cityElement.textContent = cityName;
  }

  function getCityFromCoords(lat, lon) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
      .then(response => response.json())
      .then(data => {
        console.log("Address data:", data.address);
        const address = data.address;
        const city = address.city || address.town || address.village || address.county || address.state || "your area";
        displayCity(city);
      })
      .catch(() => {
        displayCity("City unavailable");
      });
  }

  function fallbackToIP() {
    fetch("https://ipapi.co/json/")
      .then(res => res.json())
      .then(data => {
        displayCity(data.city || "your area");
      })
      .catch(() => {
        displayCity("City unavailable");
      });
  }

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        getCityFromCoords(lat, lon);
      },
      () => {
        fallbackToIP();
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  } else {
    fallbackToIP();
  }
});
</script>
</body>
</html>