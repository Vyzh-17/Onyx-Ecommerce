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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Onyx</title>
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

        /* Cart Container */
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .cart-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .continue-shopping {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: var(--transition);
        }

        .continue-shopping:hover {
            color: var(--primary-light);
        }

        /* Cart Table */
        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }

        .cart-table thead {
            background-color:rgba(255, 195, 248, 0.34) ;
            color: black;
        }

        .cart-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .cart-table td {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .product-description {
            color: var(--gray);
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-weight: 700;
            color: var(--primary);
        }

        .remove-btn {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: var(--transition);
            padding: 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-btn:hover {
            background-color: rgba(239, 35, 60, 0.1);
            transform: scale(1.1);
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-top: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .summary-label {
            color: var(--gray);
        }

        .summary-value {
            font-weight: 600;
        }

        .total-row {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--dark);
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .checkout-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Empty Cart */
        .empty-cart {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-message {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
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
            .cart-table thead {
                display: none;
            }

            .cart-table tr {
                display: block;
                margin-bottom: 1.5rem;
                border-bottom: 1px solid var(--light-gray);
                padding-bottom: 1.5rem;
            }

            .cart-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 1rem;
                border-bottom: none;
            }

            .cart-table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                color: var(--gray);
            }

            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .product-image {
                width: 100%;
                height: auto;
                max-height: 200px;
            }

            .cart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <a href="buyer_home.php" class="logo">ONYX<span>.</span></a>
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
        <a href="account.php" class="nav-link">
            <span class="material-symbols-outlined">account_circle</span>
            Profile
        </a>
        <a href="logout.php" class="nav-link">
            <span class="material-symbols-outlined">logout</span>
            Logout
        </a>
    </div>
</nav>

<!-- Cart Content -->
<div class="cart-container">
    <div class="cart-header">
        <h1 class="cart-title">
            <span class="material-symbols-outlined">shopping_cart</span>
            Your Shopping Cart
        </h1>
        <a href="buyer_home.php" class="continue-shopping">
            <span class="material-symbols-outlined">arrow_back</span>
            Continue Shopping
        </a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    $total += $row['price'];
                ?>
                <tr>
                    <td data-label="Product">
                        <div class="product-info">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                            <div>
                                <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div class="product-description"><?php echo htmlspecialchars($row['description']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Price" class="product-price">$<?php echo number_format($row['price'], 2); ?></td>
                    <td data-label="Action">
                        <button class="remove-btn" onclick="window.location.href='remove_from_cart.php?cart_id=<?php echo $row['cart_id']; ?>'">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">$<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Shipping</span>
                <span class="summary-value">Free</span>
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span>$<?php echo number_format($total, 2); ?></span>
            </div>

            <form action="checkout.php" method="POST">
                <input type="hidden" name="total" value="<?php echo $total; ?>">
                <button type="submit" class="checkout-btn">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Proceed to Checkout
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <div class="empty-icon">
                <span class="material-symbols-outlined">shopping_cart_off</span>
            </div>
            <h2 class="empty-message">Your cart is empty</h2>
            
        </div>
    <?php endif; ?>
</div>

</body>
</html>