<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

if (!isset($_GET['product_id'])) {
    echo "No product specified.";
    exit;
}

$product_id = intval($_GET['product_id']);
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

// Fetch product info
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Product not found.";
    exit;
}

$product = $result->fetch_assoc();
$total = $product['price'] * $quantity;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];

    if ($name && $phone && $address && $payment_method) {
        $order_date = date('Y-m-d H:i:s');
        $insert = $conn->prepare("INSERT INTO orders (buyer_id, product_id, quantity, order_date) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $user_id, $product_id, $quantity, $order_date);

        if ($insert->execute()) {
            $message = "✅ Your order has been placed successfully!";
        } else {
            $message = "❌ Failed to place the order.";
        }
        $insert->close();
    } else {
        $message = "❌ Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Buy Now</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 2rem auto; }
        .message { padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        form div { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input, select, textarea { width: 100%; padding: 0.5rem; }
        button { padding: 0.7rem 1.5rem; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .product-summary { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; background: #f9f9f9; }
    </style>
</head>
<body>

    <h1>Checkout - Buy Now</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') === 0 ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="product-summary">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
        <p><strong>Quantity:</strong> <?= $quantity ?></p>
        <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
    </div>

    <form method="post">
        <div>
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">--Select Payment Method--</option>
                <option value="credit_card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="cod">Cash on Delivery</option>
            </select>
        </div>

        <div>
            <label for="name">Full Name</label>
            <input id="name" name="name" type="text" required>
        </div>

        <div>
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" required>
        </div>

        <div>
            <label for="address">Delivery Address</label>
            <textarea id="address" name="address" rows="3" required></textarea>
        </div>

        <button type="submit" name="place_order">Place Order</button>
    </form>

</body>
</html>
