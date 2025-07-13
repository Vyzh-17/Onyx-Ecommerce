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
    SELECT o.id AS order_id, o.quantity, o.order_date, o.status, 
           p.name, p.price, p.image, (o.quantity * p.price) as total_price
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders | Onyx</title>
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
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
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

        /* Navigation Bar */
        .navbar {
            background-color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
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

        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .cart-count {
            background-color: var(--secondary);
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
            margin-left: 0.3rem;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            padding: 3rem 0 2rem;
            background: linear-gradient(135deg, rgba(58,12,163,0.05) 0%, rgba(247,37,133,0.05) 100%);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .page-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Orders Container */
        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Order Card */
        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .order-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            background-color: #f9f9f9;
        }

        .order-id {
            font-weight: 600;
            color: var(--dark);
        }

        .order-date {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }

        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-pending {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-details {
            padding: 1.5rem;
        }

        .product-row {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .product-row:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .product-price {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .product-quantity {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .product-total {
            font-weight: 700;
            color: var(--primary);
            min-width: 100px;
            text-align: right;
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--light-gray);
            background-color: #f9f9f9;
        }

        .order-total-label {
            font-weight: 600;
            color: var(--dark);
        }

        .order-total-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .view-details-btn {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .view-details-btn:hover {
            background: var(--primary-light);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 0;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 1.5rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-message {
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .shop-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .shop-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .product-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .product-total {
                text-align: left;
                width: 100%;
                margin-top: 0.5rem;
            }

            .order-summary {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .view-details-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <a href="index.php" class="logo">ONYX<span>.</span></a>
    <div class="nav-links">
        <a href="cart.php" class="nav-link">
            <span class="material-symbols-outlined">shopping_cart</span>
            Cart
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span class="cart-count"><?php echo array_reduce($_SESSION['cart'], function($carry, $item) { 
                    return $carry + $item['quantity']; 
                }, 0); ?></span>
            <?php endif; ?>
        </a>
        <a href="view_orders.php" class="nav-link">
            <span class="material-symbols-outlined">receipt</span>
            Orders
        </a>
        <a href="buyer_profile.php" class="nav-link">
            <span class="material-symbols-outlined">account_circle</span>
            Profile
        </a>
        <a href="logout.php" class="nav-link">
            <span class="material-symbols-outlined">logout</span>
            Logout
        </a>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <span class="material-symbols-outlined">receipt_long</span>
        Your Order History
    </h1>
    <p class="page-subtitle">View all your past and current orders with us</p>
</div>

<!-- Orders Container -->
<div class="orders-container">
    <?php if ($result->num_rows > 0): ?>
        <?php 
        $current_order_id = null;
        $first_item = true;
        while($order = $result->fetch_assoc()): 
            if ($current_order_id !== $order['order_id']) {
                if (!$first_item) {
                    // Close previous order card
                    echo '</div></div>';
                }
                $first_item = false;
                $current_order_id = $order['order_id'];
        ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">Order #<?= $order['order_id'] ?></div>
                    <div class="order-date">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></div>
                </div>
                <div class="order-status status-<?= strtolower($order['status']) ?>">
                    <?= $order['status'] ?>
                </div>
            </div>
            <div class="order-details">
        <?php } ?>
                <div class="product-row">
                    <img src="uploads/<?= htmlspecialchars($order['image']) ?>" alt="<?= htmlspecialchars($order['name']) ?>" class="product-image">
                    <div class="product-info">
                        <div class="product-name"><?= htmlspecialchars($order['name']) ?></div>
                        <div class="product-price">$<?= number_format($order['price'], 2) ?> each</div>
                        <div class="product-quantity">Quantity: <?= $order['quantity'] ?></div>
                    </div>
                    <div class="product-total">$<?= number_format($order['total_price'], 2) ?></div>
                </div>
        <?php endwhile; ?>
                
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">
                <span class="material-symbols-outlined">receipt_long</span>
            </div>
            <h2 class="empty-title">No Orders Yet</h2>
            <p class="empty-message">You haven't placed any orders with us yet. Start shopping to see your order history here.</p>
            <a href="buyer_home.php" class="shop-btn">
                <span class="material-symbols-outlined">shopping_bag</span>
                Start Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>