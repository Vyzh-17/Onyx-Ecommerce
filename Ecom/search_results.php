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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
     <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta charset="UTF-8">
    <title>Search Results for "<?php echo htmlspecialchars($search); ?>"</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin:0px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .product {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 15px;
            width: 220px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .product:hover {
            transform: translateY(-5px);
        }

        .product img {
            max-width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product h4 {
            margin: 10px 0 5px;
            font-size: 16px;
            color: #222;
        }

        .product p {
            color: #666;
            margin-bottom: 10px;
        }

        .product a {
            text-decoration: none;
            background-color:rgb(0, 0, 0);
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .product a:hover {
            background-color:rgb(0, 0, 0);
        }

        .no-results {
            text-align: center;
            color: #777;
            font-size: 18px;
            margin-top: 40px;
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
    <h2>🔍 Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>

    <?php if ($results->num_rows > 0): ?>
        <div class="product-grid">
            <?php while ($row = $results->fetch_assoc()): ?>
                <div class="product">
                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Image">
                    <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                    <p>$<?php echo htmlspecialchars($row['price']); ?></p>
                    <a href="view_product.php?id=<?php echo $row['id']; ?>">View Product</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-results">No products found matching your search.</p>
    <?php endif; ?>

</body>
</html>
