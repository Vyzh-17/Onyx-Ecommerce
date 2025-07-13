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
<html>
<head>
<script>
window.addEventListener("DOMContentLoaded", () => {
  const cityElement = document.getElementById("user-city");

  function displayCity(cityName) {
    cityElement.textContent = ` ${cityName}`;
  }

  function getCityFromCoords(lat, lon) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
      .then(response => response.json())
      .then(data => {
        console.log("Address data:", data.address); // for debugging
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
        // Permission denied or error — fallback to IP-based location
        fallbackToIP();
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  } else {
    // Geolocation not supported — fallback
    fallbackToIP();
  }
});
</script>





  <title>eCommerce Home</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
  
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f9f9;
    }

    .navbar {
      background-color:rgb(255, 255, 255);
      padding: 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: black;
    }

    .navbar a {
      color: black;
      margin-left: 15px;
      text-decoration: none;
      font-weight: bold;
    }

    .navbar a:hover {
      text-decoration: none;
    }

    .search-bar {
      padding: 1em;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .search-bar form {
      display: flex;
      max-width: 600px;
      margin: 0 auto;
    }

    .search-bar input[type="text"] {
      flex: 1;
      padding: 10px;
      font-size: 16px;
      border-radius: 8px 0 0 8px;
      border: 1px solid #ccc;
    }

    .search-bar button {
      padding: 10px 20px;
      background-color:rgb(0, 0, 0);
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 0 8px 8px 0;
      cursor: pointer;
    }

    .search-bar button:hover {
      background-color:rgb(72, 72, 72);
    }

    .section-title {
      padding: 1em 2em 0.5em;
      font-size: 20px;
      font-weight: bold;
      color: #333;
    }

    .products {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
      padding: 1em 2em 2em;
    }

    .product-card {
      background-color: white;
      padding: 1em;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      text-align: center;
    }

    .product-card h3 {
      margin: 0.5em 0;
    }

    .product-card p {
      margin: 0.5em 0;
      color: #555;
    }
.image-carousel {
  width: 2000;
  max-height: 1000px;
  overflow: hidden;
  position: relative;
  margin-bottom: 1em;
}

.image-track {
  display: flex;
  width: 300%%;
  animation: slide 12s infinite;
}

.image-track img {
  width: 100%;
  height: 400px;
  object-fit: cover;
  flex-shrink: 0;
  transition: transform 0.5s ease;
}

@keyframes slide {
  0%, 33% { transform: translateX(0%); }
  36%, 66% { transform: translateX(-100%); }
  69%, 99% { transform: translateX(-200%); }
  100% { transform: translateX(0%); }
}
.category-section {
  display: flex;
  justify-content: center;
  gap: 0.8em;
  flex-wrap: wrap;
  margin: 1.5em 0;
}

.category-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #fff;
  padding: 0.6em;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  width: 80px;
  transition: transform 0.2s;
  cursor: pointer;
}

.category-card:hover {
  transform: translateY(-3px);
}

.category-icon {
  font-size: 1.5em;
  margin-bottom: 0.3em;
}

.category-label {
  font-weight: 500;
  font-size: 0.75em;
  text-align: center;
}
.sh{
  width: 8000;
  max-height: 3000px;
  overflow: hidden;
  position: relative;
  margin-bottom: 1em;

}
  </style>
</head>
<body>

<div class="navbar">
  <div><strong>Onxy</strong></div>
  <div>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a id="user-city" style="font-size: 0.9em; color: black;"> Detecting city...</a>
       <a href="sellerreg.php">Become a Seller</a>
        

      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
</div>

<div class="search-bar">
  <form method="GET" action="search_results.php">
    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit"><span class="material-symbols-outlined">
search
</span></button>
  </form>
</div>

<div class="category-section">
  <div class="category-card">
    <div class="category-icon">🛒</div>
    <div class="category-label">Grocery</div>
  </div>
  <div class="category-card">
    <div class="category-icon">💊</div>
    <div class="category-label">Pharmacy</div>
  </div>
  <div class="category-card">
    <div class="category-icon">🍔</div>
    <div class="category-label">Food</div>
  </div>
  <div class="category-card">
    <div class="category-icon">🛠️</div>
    <div class="category-label">Artisans</div>
  </div>
  <div class="category-card">
    <div class="category-icon">📱</div>
    <div class="category-label">Electronics</div>
  </div>
  <div class="category-card">
    <div class="category-icon">💄</div>
    <div class="category-label">Beauty</div>
  </div>
  <div class="category-card">
    <div class="category-icon">🏠</div>
    <div class="category-label">Home</div>
  </div>
  <div class="category-card">
    <div class="category-icon">👗</div>
    <div class="category-label">Fashion</div>
  </div>
</div>
<img src="e1.jpg" style="width: 99vw; height: 99vh; object-fit: cover;padding:15px;" alt="Banner">



<<!-- Featured Products -->
<div class="section-title">Featured Products</div>
<div class="products">
  <?php if ($featuredProducts->num_rows > 0): ?>
    <?php while($row = $featuredProducts->fetch_assoc()): ?>
      
      <a href="view_product.php?id=<?php echo urlencode($row['id']); ?>" style="text-decoration: none; color: inherit;">
        <div class="product-card">
          <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" style="width: 100%; height: 200px; object-fit: cover;">
          <h3><?php echo htmlspecialchars($row['name']); ?></h3>
          
          <p><strong>$<?php echo htmlspecialchars($row['price']); ?></strong></p>
        </div>
      </a>

    <?php endwhile; ?>
  <?php else: ?>
    <p style="padding: 2em;">No featured products found.</p>
  <?php endif; ?>
</div>


<!-- Most Popular -->
<div class="section-title">Most Popular</div>
<div class="products">
  <?php if ($popularProducts->num_rows > 0): ?>
    <?php while($row = $popularProducts->fetch_assoc()): ?>
       <a href="view_product.php?id=<?php echo urlencode($row['id']); ?>" style="text-decoration: none; color: inherit;">
        <div class="product-card">
          <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image" style="width: 100%; height: 200px; object-fit: cover;">
          <h3><?php echo htmlspecialchars($row['name']); ?></h3>
          
          <p><strong>$<?php echo htmlspecialchars($row['price']); ?></strong></p>
        </div>
      </a>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="padding: 2em;">No popular products found.</p>
  <?php endif; ?>
</div>

</body>
</html>

