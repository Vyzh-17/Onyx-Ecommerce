<?php
session_start();
include("config.php");

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $searchTerm = "%" . $search . "%";
    
    $stmt1 = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt1->bind_param("s", $searchTerm);
    $stmt2 = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY popularity DESC");
    $stmt2->bind_param("s", $searchTerm);
} else {
    $stmt1 = $conn->prepare("SELECT * FROM products WHERE featured = 1");
    $stmt2 = $conn->prepare("SELECT * FROM products ORDER BY popularity DESC");
}

$stmt1->execute();
$featuredProducts = $stmt1->get_result();

$stmt2->execute();
$popularProducts = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onyx - Premium Shopping Experience</title>
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

    .search-bar {
      padding: 1rem 2rem;
      background-color: white;
      box-shadow: 0 2px 15px rgba(0,0,0,0.03);
    }

    .search-form {
      display: flex;
      max-width: 800px;
      margin: 0 auto;
      border-radius: 50px;
      overflow: hidden;
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      border: 1px solid #e0e0e0;
    }

    .search-input {
      flex: 1;
      padding: 0.8rem 1.5rem;
      font-size: 1rem;
      border: none;
      outline: none;
    }

    .search-input::placeholder {
      color: var(--gray);
    }

    .search-button {
      padding: 0 1.5rem;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      border: none;
      color: white;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .search-button:hover {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
    }

    .hero-banner {
      width: 100%;
      height: 70vh;
      object-fit: cover;
      margin-bottom: 2rem;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Circular Categories Section */
    .circular-categories {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .categories-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 2rem;
      justify-items: center;
    }

    .category-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .icon-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
    }

    .icon-circle:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .category-item:hover .icon-circle {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .category-item:hover .icon-circle:before {
      opacity: 1;
    }

    .category-item:hover .icon-circle i {
      color: white;
      transform: scale(1.1);
    }

    .icon-circle i {
      font-size: 30px;
      color: var(--primary);
      position: relative;
      z-index: 1;
      transition: all 0.3s ease;
    }

    .category-name {
      color: var(--dark);
      font-size: 14px;
      font-weight: 500;
      text-align: center;
      transition: all 0.2s ease;
    }

    .category-item:hover .category-name {
      color: var(--primary);
      font-weight: 600;
    }

    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark);
      margin: 2rem 2rem 1rem;
      position: relative;
      padding-left: 1rem;
    }

    .section-title::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 4px;
      background: var(--primary);
      border-radius: 2px;
    }

    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.5rem;
      padding: 0 2rem 2rem;
      max-width: 1400px;
      margin: 0 auto;
      text-decoration:none;
    }

    .product-card {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .product-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .product-info {
      padding: 1.2rem;
    }

    .product-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .product-price {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--primary);
    }

    .product-rating {
      display: flex;
      align-items: center;
      margin-top: 0.5rem;
      color: var(--warning);
      font-size: 0.9rem;
    }

    .empty-message {
      padding: 2rem;
      text-align: center;
      color: var(--gray);
      grid-column: 1 / -1;
    }
  .hero-container {
  width: 100%;
  background-color: #f5f5f7;
  padding: 0;
  margin-bottom: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-banner {
  width: 100%;
  height: auto;
  max-height: 100vh;
  margin: 0 auto 2rem;
  display: block;
}
    .footer {
      background-color: var(--dark);
      color: white;
      padding: 3rem 2rem;
      text-align: center;
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
      transition: var(--transition);
    }

    .footer-link:hover {
      color: var(--secondary);
    }

    .copyright {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }
    @media (max-width: 768px) {
      .navbar {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
      }
      
      .nav-links {
        width: 100%;
        justify-content: space-between;
      }
      
      .categories-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
      }
      
      .icon-circle {
        width: 60px;
        height: 60px;
      }
      
      .icon-circle i {
        font-size: 24px;
      }
      
      .category-name {
        font-size: 12px;
      }
      
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        padding: 0 1rem 1rem;
      }
      

    }

    @media (max-width: 480px) {
      .categories-grid {
        grid-template-columns: repeat(3, 1fr);
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

<div class="search-bar">
  <form class="search-form" method="GET" action="search_results.php">
    <input type="text" name="search" class="search-input" placeholder="Search for products, brands and more..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="search-button">
      <span class="material-symbols-outlined">search</span>
    </button>
  </form>
</div>



<!-- Circular Categories Section -->
<div class="circular-categories">
 
  <div class="categories-grid">
    <a href="" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-shopping-basket"></i>
      </div>
      <span class="category-name">Grocery</span>
    </a>
    
    <a href="#" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-pills"></i>
      </div>
      <span class="category-name">Pharmacy</span>
    </a>
    
    <a href="#" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-hamburger"></i>
      </div>
      <span class="category-name">Food</span>
    </a>
    
    <a href="#" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-tools"></i>
      </div>
      <span class="category-name">Tools</span>
    </a>
    
    <a href="category.php?category=electronics" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-mobile-alt"></i>
      </div>
      <span class="category-name">Electronics</span>
    </a>
    
    <a href="category.php?category=fashion" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-tshirt"></i>
      </div>
      <span class="category-name">Fashion</span>
    </a>
    
 
    
    <a href="category.php?category=home" class="category-item">
      <div class="icon-circle">
        <i class="fas fa-gamepad"></i>
      </div>
      <span class="category-name">Gaming</span>
    </a>
  </div>
</div>
<div class="hero-container">
  <img src="e1.jpg" class="hero-banner" alt="Premium Shopping Experience">
</div>
<!-- Featured Products -->
<h2 class="section-title">Featured Products</h2>
<div class="products-grid">
  <?php if ($featuredProducts->num_rows > 0): ?>
    <?php while($row = $featuredProducts->fetch_assoc()): ?>
      <a href="view_product.php?id=<?php echo urlencode($row['id']); ?>" class="product-card">
        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
        <div class="product-info">
          <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
          <div class="product-price">$<?php echo htmlspecialchars($row['price']); ?></div>
          <div class="product-rating">
            <span class="material-symbols-outlined filled">star</span>
            <span>4.5</span>
          </div>
        </div>
      </a>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-message">No featured products found</div>
  <?php endif; ?>
</div>

<!-- Most Popular -->
<h2 class="section-title">Most Popular</h2>
<div class="products-grid">
  <?php if ($popularProducts->num_rows > 0): ?>
    <?php while($row = $popularProducts->fetch_assoc()): ?>
      <a href="view_product.php?id=<?php echo urlencode($row['id']); ?>" class="product-card">
        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
        <div class="product-info">
          <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
          <div class="product-price">$<?php echo htmlspecialchars($row['price']); ?></div>
          <div class="product-rating">
            <span class="material-symbols-outlined">star</span>
            <span>4.8</span>
          </div>
        </div>
      </a>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="empty-message">No popular products found</div>
  <?php endif; ?>
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

