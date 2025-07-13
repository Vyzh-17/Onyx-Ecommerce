<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders with product details
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.quantity, o.order_date, p.name, p.price, p.image
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.buyer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>Your Orders</title>
    
    <style>
        
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem;  border-radius: 10px;}
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 80px; height: auto; }
        .no-orders { text-align: center; margin-top: 2rem; font-size: 1.2rem; color: #555; }
         * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color:rgb(255, 255, 255);
    }


    .navbar {
      background-color:rgb(0, 0, 0);
      padding: 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: white;
    }

    .navbar a {
      color: white;
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
    .box {
      max-width: 1000px;
      margin: 40px auto;
      
      background-color: #fff;
      
     
    }
    </style>
</head>
<body>
<div class="navbar">
  <div><strong>Onxy
  </strong></div>
  <div>
    <?php if (isset($_SESSION['user_id'])): ?>
     
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
      
      <a href="view_orders.php"><span class="material-symbols-outlined">
orders
</span></a>
      <a href="buyer_profile.php"><span class="material-icons">
account_circle
</span></a>
       <a href="logout.php"><span class="material-symbols-outlined">
logout
</span></a>
    <?php else: ?>
      <a id="user-city" style="font-size: 0.9em; color: black;">📍 Detecting city...</a>
      <a href="login.php">Become a Seller</a>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
</div>
<h1>Your Orders</h1>

<?php if ($result->num_rows > 0): ?>
    <div class="box">
    <table>
        <thead>
            <tr>
               
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price per Unit ($)</th>
                <th>Total Price ($)</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = $result->fetch_assoc()): ?>
                <tr>
                    
                    <td><img src="uploads/<?= htmlspecialchars($order['image']) ?>" alt="Product Image"></td>
                    <td><?= htmlspecialchars($order['name']) ?></td>
                    <td><?= htmlspecialchars($order['quantity']) ?></td>
                    <td><?= number_format($order['price'], 2) ?></td>
                    <td><?= number_format($order['price'] * $order['quantity'], 2) ?></td>
                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-orders">You have no orders yet.</p>
<?php endif; ?>
</div>
<div style="text-align: center; margin-top: 30px;">
    <a href="buyer_home.php" style="display: inline-block; padding: 10px 20px; background: #555; color: white; text-decoration: none; border-radius: 5px;">
        Back 
    </a>
</div>

</body>
</html>
