<?php
session_start();
include("config.php");

if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
    echo "No search term provided.";
    exit;
}

$search = trim($_GET['search']);
$searchTerm = "%" . $search . "%";

$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results for "<?php echo htmlspecialchars($search); ?>" | Onyx</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
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

    .search-header {
      text-align: center;
      padding: 2rem 0 1rem;
    }

    .search-title {
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 0.5rem;
    }

    .search-term {
      color: var(--primary);
      font-weight: 700;
    }

    .results-count {
      color: var(--gray);
      font-size: 1rem;
    }

    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      padding: 1rem 2rem 3rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    .product-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .product-image-container {
      height: 220px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fafafa;
    }

    .product-image {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-image {
      transform: scale(1.05);
    }

    .product-info {
      padding: 1.5rem;
    }

    .product-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-price {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--primary);
      margin: 0.5rem 0;
    }

    .product-rating {
      display: flex;
      align-items: center;
      margin-top: 0.5rem;
      color: var(--warning);
      font-size: 0.9rem;
    }

    .view-btn {
      display: inline-block;
      width: 100%;
      padding: 0.75rem;
      margin-top: 1rem;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .view-btn:hover {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      box-shadow: 0 5px 15px rgba(58, 12, 163, 0.2);
    }

    .no-results {
      text-align: center;
      padding: 3rem;
      color: var(--gray);
      font-size: 1.2rem;
      grid-column: 1 / -1;
    }

    .no-results-icon {
      font-size: 3rem;
      color: var(--gray);
      margin-bottom: 1rem;
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
      .navbar {
        padding: 1rem;
      }
      
      .nav-links {
        gap: 1rem;
      }
      
      .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        padding: 1rem;
      }
      
      .search-title {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .product-grid {
        grid-template-columns: 1fr;
      }
      
      .nav-links {
        gap: 0.75rem;
      }
    }
  </style>
</head>
<body>

<div class="navbar">
  <a href="index.php" class="logo">ONYX<span>.</span></a>
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

<div class="search-header">
  <h1 class="search-title">Search Results for "<span class="search-term"><?php echo htmlspecialchars($search); ?></span>"</h1>
  <p class="results-count"><?php echo $results->num_rows; ?> items found</p>
</div>

<div class="product-grid">
  <?php if ($results->num_rows > 0): ?>
    <?php while ($row = $results->fetch_assoc()): ?>
      <div class="product-card">
        <div class="product-image-container">
          <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
        </div>
        <div class="product-info">
          <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
          <div class="product-price">$<?php echo htmlspecialchars($row['price']); ?></div>
          <div class="product-rating">
            <span class="material-symbols-outlined">star</span>
            <span>4.5</span>
            <span>(24)</span>
          </div>
          <a href="view_product.php?id=<?php echo $row['id']; ?>" class="view-btn">View Product</a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="no-results">
      <span class="material-symbols-outlined no-results-icon">search_off</span>
      <p>No products found matching your search</p>
      <p>Try different keywords or check back later</p>
    </div>
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