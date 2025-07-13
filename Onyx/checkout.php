<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$total = 0;
$cartItems = null;

// Handle order placing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($payment_method && $name && $phone && $address) {
        // Determine items to order: single product or cart
        if (isset($_GET['product_id'])) {
            // Single product order
            $product_id = intval($_GET['product_id']);
            $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

            $stmt = $conn->prepare("INSERT INTO orders (buyer_id, product_id, quantity, order_date, address, phone, payment_method, name) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $user_id, $product_id, $quantity, $address, $phone, $payment_method, $name);

            if ($stmt->execute()) {
                $message = "Your order has been placed successfully!";
            } else {
                $message = "Failed to place the order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Cart order - fetch all cart items
            $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $cartResult = $stmt->get_result();

            $allSuccess = true;
            while ($row = $cartResult->fetch_assoc()) {
                $product_id = $row['product_id'];
                $quantity = $row['quantity'];
                $insert = $conn->prepare("INSERT INTO orders (buyer_id, product_id, quantity, order_date, address, phone, payment_method, name) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
                $insert->bind_param("iiissss", $user_id, $product_id, $quantity, $address, $phone, $payment_method, $name);

                if (!$insert->execute()) {
                    $allSuccess = false;
                    $message = "Failed to place an order: " . $insert->error;
                    $insert->close();
                    break;
                }
                $insert->close();
            }
            $stmt->close();

            if ($allSuccess) {
                // Clear the cart
                $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $delete->bind_param("i", $user_id);
                $delete->execute();
                $delete->close();

                $message = "Your order has been placed successfully!";
            }
        }
    } else {
        $message = "Please fill all fields.";
    }
}

// Fetch cart or single product info for displaying the checkout page
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Product not found.";
        exit;
    }

    $product = $result->fetch_assoc();

    $cartItems = [ 
        [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ]
    ];

    $total = $product['price'] * $quantity;
} else {
    $stmt = $conn->prepare("SELECT c.product_id, p.name, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();

    $cartItems = [];
    while ($row = $cartResult->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Onyx</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --accent: #f72585;
            --dark: #14213d;
            --light: #f8f9fa;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
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
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
        }

        .checkout-container {
            display: flex;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            gap: 2rem;
        }

        .checkout-summary {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
            height: fit-content;
        }

        .checkout-form {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .order-items {
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1.5rem;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .item-price {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .item-quantity {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-top: 2px solid var(--light-gray);
            margin-top: 1rem;
        }

        .total-label {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .total-amount {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn {
            display: block;
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
            text-align: center;
        }

        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-cart {
            text-align: center;
            padding: 2rem;
        }

        .empty-cart i {
            font-size: 3rem;
            color: var(--light-gray);
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .back-link {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-link:hover {
            background: var(--secondary);
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
            }
            
            .checkout-summary,
            .checkout-form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-summary">
            <h1 class="page-title">
                <i class="fas fa-shopping-bag"></i>
                Order Summary
            </h1>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Failed') === false ? 'message-success' : 'message-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <a href="buyer_home.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            <?php elseif ($total > 0): ?>
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <img src="uploads/<?php echo htmlspecialchars($product['image'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-total">
                    <span class="total-label">Total:</span>
                    <span class="total-amount">$<?php echo number_format($total, 2); ?></span>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h2 class="empty-title">Your cart is empty</h2>
                    <a href="buyer_home.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total > 0 && !$message): ?>
        <div class="checkout-form">
            <h1 class="page-title">
                <i class="fas fa-credit-card"></i>
                Payment Details
            </h1>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="" disabled selected>Select payment method</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="UPI">UPI Payment</option>
                        <option value="Card">Credit/Debit Card</option>
                        <option value="Net Banking">Net Banking</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Delivery Address</label>
                    <textarea name="address" id="address" class="form-control" rows="4" placeholder="Enter your complete delivery address" required></textarea>
                </div>

                <button type="submit" name="place_order" class="btn">
                    <i class="fas fa-check-circle"></i>
                    Place Order
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>