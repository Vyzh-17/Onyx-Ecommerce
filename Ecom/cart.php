<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT c.id as cart_id, p.id as product_id, p.name, p.description, p.price, p.image 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
<style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fff;
      color: #000;
      margin: 0;
      padding: 0;
    }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #000;
      color: #fff;
      padding: 15px 25px;
    }

    .navbar a {
      color: #fff;
      text-decoration: none;
      margin-left: 20px;
      font-size: 1rem;
    }

    .navbar a:hover {
      text-decoration: none;
    }

    .box {
      max-width: 1000px;
      margin: 40px auto;
      padding: 25px;
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
    }

    h3 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      border-bottom: 1px solid #000;
      padding-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }

    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ccc;
    }

    th {
      background-color: #f2f2f2;
    }

    img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
    }

    .price {
      font-weight: bold;
    }

    .remove-btn {
      color: black;
      text-decoration: none;
      font-size: 1.2rem;
    }

    .remove-btn:hover {
      text-decoration: underline;
    }

    .total {
      font-size: 1.2rem;
      font-weight: bold;
      text-align: right;
    }

    .checkout-btn {
  background-color: #000;
  color: #fff;
  padding: 12px 25px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
}
.checkout-btn:hover {
  background-color: #333;
}
.checkout-wrapper {
  text-align: right;
  margin-top: 20px;
}


    .empty-cart {
      font-size: 1.1rem;
      color: #333;
      text-align: center;
    }

    .back-link {
      color: black;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
      <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
 <style>
   

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

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

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
<div class="box">
  <h3>🛒 Your Cart</h3>

  <?php if ($result->num_rows > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Image</th>
        <th>Description</th>
        <th>Price ($)</th>
        <th>Remove</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): 
          $total += $row['price'];
      ?>
      <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt=""></td>
        <td><?php echo htmlspecialchars($row['description']); ?></td>
        <td class="price">$<?php echo number_format($row['price'], 2); ?></td>
        <td><a class="remove-btn" href="remove_from_cart.php?cart_id=<?php echo $row['cart_id']; ?>">❌</a></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <p class="total">Total: $<?php echo number_format($total, 2); ?></p>

  <div class="checkout-wrapper">
  <form action="checkout.php" method="POST">
    <input type="hidden" name="total" value="<?php echo $total; ?>">
    <button type="submit" class="checkout-btn">Proceed to Checkout</button>
  </form>
</div>


  <?php else: ?>
  <p class="empty-cart">Your cart is empty. <a class="back-link" href="buyer_home.php">Go back</a></p>
  <?php endif; ?>
</div>
</body>
</html>



