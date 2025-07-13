<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '1') {
    header('Location: sellerlogin.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

if (isset($_POST['delivered_order_id'])) {
    $order_id = intval($_POST['delivered_order_id']);
    $stmtDel = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmtDel->bind_param("i", $order_id);
    $stmtDel->execute();
    $stmtDel->close();
    header("Location: pending.php");
    exit();
}

$sql = "SELECT o.id, o.buyer_id, o.product_id, o.quantity, o.order_date, o.address, o.phone, o.payment_method, o.name, p.name AS product_name, p.image
        FROM orders o 
        JOIN products p ON o.product_id = p.id
        WHERE o.status = 'pending' AND p.seller_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            
            margin:0px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .order-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .order-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            width: 350px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .order-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .order-info {
            font-size: 14px;
            color: #444;
            margin-bottom: 8px;
        }

        .order-info strong {
            color: #000;
        }

        .btn-deliver {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .btn-deliver:hover {
            background-color: #218838;
        }

        .no-orders {
            text-align: center;
            font-size: 18px;
            color: #777;
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
  <div><strong><a href="seller_dashboard.php">Seller Dashboard</a></strong></div>
  <div>
    <a href="add_product.php" class="logout"><span class="material-symbols-outlined">
add
</span></a>
      <a href="pending.php" class="logout"><span class="material-symbols-outlined">
pending_actions
</span></a>
   
    <a href="seller_profile.php" class="logout"><span class="material-icons">
account_circle
</span></a>
    <a href="logout.php" class="logout"><span class="material-symbols-outlined">
logout
</span></a>
  </div>
</div>
<h1>Pending Orders</h1>

<?php if ($result->num_rows > 0): ?>
    <div class="order-container">
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="order-card">
                <img src="uploads/<?php echo htmlspecialchars($order['image']); ?>" alt="Product Image">
                <div class="order-info"><strong>Product:</strong> <?php echo htmlspecialchars($order['product_name']); ?></div>
                <div class="order-info"><strong>Buyer:</strong> <?php echo htmlspecialchars($order['name']); ?></div>
                <div class="order-info"><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></div>
                <div class="order-info"><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></div>
                <div class="order-info"><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></div>
                <div class="order-info"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></div>
                <div class="order-info"><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>

                <form method="POST" onsubmit="return confirm('Mark this order as delivered?');">
                    <input type="hidden" name="delivered_order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" class="btn-deliver">Mark as Delivered</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p class="no-orders">No pending orders found.</p>
<?php endif; ?>

</body>
</html>
