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
                $message = " Your order has been placed successfully!";
            } else {
                $message = "❌ Failed to place the order: " . $stmt->error;
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
                    $message = "❌ Failed to place an order: " . $insert->error;
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

                $message = " Your order has been placed successfully!";
            }
        }
    } else {
        $message = " Please fill all fields.";
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
<html>
<head>
    <title>Checkout</title>
    <style>
        /* Reset some default styles */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding: 40px 20px;
            color: #333;
        }
        .box {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 700;
            text-align: center;
            font-size: 1.8em;
        }
        label {
            font-weight: 600;
            margin-top: 20px;
            display: block;
            color: #555;
        }
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            border: 1.8px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
            font-family: inherit;
            resize: vertical;
        }
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.3);
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #28a745;
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1em;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 30px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #218838;
        }
        .message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        a {
            color: #28a745;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 15px;
        }
        a:hover {
            text-decoration: underline;
        }
        p strong {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
<div class="box">
    <h2> Checkout</h2>

    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <a href="buyer_home.php">← Back to Home</a>
    <?php elseif ($total > 0): ?>
        <p><strong>Total to Pay:</strong> $<?php echo number_format($total, 2); ?></p>

        <form method="POST" novalidate>
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="" disabled selected>-- Select --</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="UPI">UPI</option>
                <option value="Card">Card</option>
            </select>

            <label for="name">Full Name:</label>
            <input type="text" name="name" id="name" required placeholder="Enter your full name">

            <label for="phone">Phone Number:</label>
            <input type="text" name="phone" id="phone" required placeholder="Enter your phone number">

            <label for="address">Address:</label>
            <textarea name="address" id="address" rows="4" required placeholder="Enter your delivery address"></textarea>

            <button type="submit" name="place_order" class="btn">Place Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty. <a href="buyer_home.php">Go back</a></p>
    <?php endif; ?>
</div>
</body>
</html>
