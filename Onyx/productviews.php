<?php
session_start();
include("config.php");

// Ensure the user is a logged-in seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '1') {
    die("Unauthorized access. Please log in as a seller.");
}

$seller_id = $_SESSION['user_id'];

// Check if product ID is passed
if (!isset($_GET['id'])) {
    die("No product specified.");
}

$product_id = intval($_GET['id']);

// Fetch product only if it belongs to the logged-in seller
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Product not found or unauthorized access.");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
           margin:0px;
            background-color: #f5f5f5;
        }
        .product-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .product-image {
            float: left;
            margin-right: 30px;
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-details {
            overflow: auto;
        }
        h2 {
            margin-top: 0;
        }
        p {
            margin: 8px 0;
            line-height: 1.5;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            background: #444;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #222;
            
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
  <div><strong>Seller Dashboard</strong></div>
  <div>
    <a href="add_product.php" class="logout">add</a>
      <a href="pending.php" class="logout"><span class="material-symbols-outlined">
pending_actions
</span></a>
    <a href="seller_products.php" class="logout">products</a>
    <a href="seller_profile.php" class="logout"><span class="material-icons">
account_circle
</span></a>
    <a href="logout.php" class="logout"><span class="material-symbols-outlined">
logout
</span></a>
  </div>
</div><br><br>
<div class="product-container">
    <div class="product-details">
        <img class="product-image" src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
      
 
        <?php if ($product['featured']): ?>
            <p><strong style="color: green;">🌟 Featured Product</strong></p>
        <?php endif; ?>
    </div>

    <a href="seller_dashboard.php" class="back-btn">← Back to My Products</a>
</div>

</body>
</html>
