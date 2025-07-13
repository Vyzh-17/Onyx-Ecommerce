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
<html>
<head>
    <title>View Product</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
        <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }

        .product-container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            display: flex;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .product-left {
            flex: 1;
            background: #f9f9f9;
            padding: 30px;
            text-align: center;
        }

        .product-left img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 6px;
        }

        .product-left h2 {
            margin-top: 20px;
            font-size: 22px;
            color: #222;
        }

        .product-right {
            flex: 1;
            padding: 30px;
        }

        .product-right p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .product-right strong {
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        input[type="number"] {
            width: 60px;
            padding: 6px;
            margin-right: 10px;
        }

        button {
            padding: 10px 18px;
            margin-right: 10px;
            background: rgb(0, 0, 0);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background:rgb(31, 31, 31);
        }

        @media (max-width: 768px) {
            .product-container {
                flex-direction: column;
            }
            .product-left, .product-right {
                padding: 20px;
            }
        }
    </style>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Symbols+Outlined" rel="stylesheet">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
   
     <style>
   
body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fff;
      color: #000;
      margin: 0;
      padding: 0;
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
.actions{
    display:flex;
}
    
    
   
  </style>
</head>
<body>
    <div class="navbar">
  <div><strong><a href="buyer_home.php">Onxy</a>
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
<br><br>
   <div class="product-container">
    <div class="product-left">
         
        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
       
    </div>
    <div class="product-right">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>

        <div class="actions">
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="add">Add to Cart </button>
            </form>

            <form action="buy_now.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="buy">Buy Now </button>
            </form>

        </div>
        <div style=" margin-top: 10px;">
    <a href="buyer_home.php" style="display: inline-block; padding: 10px 20px; background: #555; color: white; text-decoration: none; border-radius: 5px;">
        Back
    </a>
</div>

    </div>
     
</div>

</body>
</html>
