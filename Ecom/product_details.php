<?php
session_start();
include("config.php");

if (!isset($_GET['id'])) {
    echo "Product not specified.";
    exit;
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Product not found.";
    exit;
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <style>
        .container {
            max-width: 700px;
            margin: auto;
            padding: 20px;
            font-family: Arial;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f7f7f7;
        }
        img {
            max-width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .actions {
            margin-top: 20px;
        }
        .actions form {
            display: inline-block;
            margin-right: 10px;
        }
        .actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .add { background: #4CAF50; color: white; }
        .buy { background: #2196F3; color: white; }
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
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>

        <div class="actions">
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="add">Add to Cart 🛒</button>
            </form>

            <form action="buy_now.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="buy">Buy Now ⚡</button>
            </form>
        </div>
    </div>
</body>
</html>
