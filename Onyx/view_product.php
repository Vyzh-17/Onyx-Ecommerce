<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$product_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($product['name']); ?> | Onyx</title>
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

    .product-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    .product-card {
      display: flex;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    .product-gallery {
      flex: 1;
      padding: 2rem;
      background: #fafafa;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 500px;
    }

    .product-image {
      max-width: 100%;
      max-height: 400px;
      object-fit: contain;
      border-radius: 8px;
      transition: transform 0.3s ease;
    }

    .product-image:hover {
      transform: scale(1.03);
    }

    .product-details {
      flex: 1;
      padding: 3rem;
      display: flex;
      flex-direction: column;
    }

    .product-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--dark);
    }

    .product-price {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--primary);
      margin: 1rem 0;
    }

    .product-description {
      color: var(--gray);
      margin-bottom: 2rem;
      line-height: 1.7;
    }

    .product-meta {
      margin: 1rem 0;
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      color: var(--gray);
    }

    .meta-item i {
      color: var(--primary);
    }

    .product-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      flex-wrap: wrap;
    }

    .btn {
      padding: 0.875rem 1.75rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 1rem;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(58, 12, 163, 0.2);
    }

    .btn-secondary {
      background-color: white;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    .btn-secondary:hover {
      background-color: #f0f2ff;
      transform: translateY(-2px);
    }

    .back-link {
      margin-top: 1.5rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--gray);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .back-link:hover {
      color: var(--primary);
    }

    .rating {
      display: flex;
      align-items: center;
      margin: 1rem 0;
    }

    .stars {
      color: var(--warning);
      display: flex;
      gap: 0.2rem;
    }

    .review-count {
      margin-left: 0.5rem;
      color: var(--gray);
      font-size: 0.9rem;
    }

    .footer {
      background-color: var(--dark);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
      margin-top: 3rem;
    }

    .footer-links {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .footer-link {
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .footer-link:hover {
      color: var(--secondary);
    }

    .copyright {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .product-card {
        flex-direction: column;
      }
      
      .product-gallery, .product-details {
        padding: 1.5rem;
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .navbar {
        padding: 1rem;
      }
      
      .nav-links {
        gap: 1rem;
      }
    }

    @media (max-width: 480px) {
      .product-container {
        padding: 0 1rem;
      }
      
      .product-title {
        font-size: 1.5rem;
      }
      
      .product-price {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<div class="navbar">
  <a href="buyer_home.php" class="logo">ONYX<span>.</span></a>
  <div class="nav-links">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="location-badge">
        <span class="material-symbols-outlined">location_on</span>
        <span id="user-city">Detecting city...</span>
      </div>
      <a href="cart.php"><span class="material-symbols-outlined">shopping_cart</span>
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

<div class="product-container">
  <div class="product-card">
    <div class="product-gallery">
      <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
    </div>
    
    <div class="product-details">
      <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
      
      <div class="rating">
        <div class="stars">
          <span class="material-symbols-outlined">star</span>
          <span class="material-symbols-outlined">star</span>
          <span class="material-symbols-outlined">star</span>
          <span class="material-symbols-outlined">star</span>
          <span class="material-symbols-outlined">star_half</span>
        </div>
        <span class="review-count">(42 reviews)</span>
      </div>
      
      <div class="product-price">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></div>
      
      <div class="product-meta">
        <div class="meta-item">
          <i class="fas fa-box-open"></i>
          <span>In Stock: <?php echo htmlspecialchars(rand(5, 50)); ?> units</span>
        </div>
        <div class="meta-item">
          <i class="fas fa-truck"></i>
          <span>Free delivery</span>
        </div>
        <div class="meta-item">
          <i class="fas fa-shield-alt"></i>
          <span>1-year warranty</span>
        </div>
      </div>
      
      <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
      
      <div class="product-actions">
        <form action="add_to_cart.php" method="POST" style="flex: 1;">
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
          <button type="submit" class="btn btn-secondary">
            <span class="material-symbols-outlined">add_shopping_cart</span>
            Add to Cart
          </button>
        </form>
        
        <form action="buy_now.php" method="POST" style="flex: 1;">
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
          <button type="submit" class="btn btn-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            Buy Now
          </button>
        </form>
      </div>
      
      <a href="buyer_home.php" class="back-link">
        <span class="material-symbols-outlined">arrow_back</span>
        Continue Shopping
      </a>
    </div>
  </div>
</div>

<footer class="footer">
  <div class="footer-links">
    <a href="about.php" class="footer-link">About Us</a>
    <a href="contact.php" class="footer-link">Contact</a>
    <a href="privacy.php" class="footer-link">Privacy Policy</a>
    <a href="terms.php" class="footer-link">Terms of Service</a>
    <a href="faq.php" class="footer-link">FAQ</a>
  </div>
  <p class="copyright">© <?php echo date('Y'); ?> Onyx. All rights reserved.</p>
</footer>

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