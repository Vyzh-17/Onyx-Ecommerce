<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '1') {
    die("Unauthorized access.");
}

$seller_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Product ID missing.");
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found or unauthorized.");
}

$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, featured = ? WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ssdssii", $name, $description, $price, $image_name, $featured, $product_id, $seller_id);
        } else {
            echo "Image upload failed.";
            exit;
        }
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, featured = ? WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("ssdiii", $name, $description, $price, $featured, $product_id, $seller_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location.href = 'products.php';</script>";
        exit;
    } else {
        echo "Error updating product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f5f5f5;
            padding: 40px;
            margin: 0;
        }

        .edit-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 8px;
        }

        .checkbox-label {
            font-weight: normal;
            font-size: 0.95rem;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color:rgb(0, 0, 0);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color:rgb(0, 0, 0);
        }

        .back-link {
            display: block;
            margin-top: 25px;
            text-align: center;
            text-decoration: none;
            color:rgb(0, 0, 0);
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Product Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>

        <label for="price">Price ($)</label>
        <input type="number" name="price" id="price" step="0.01" value="<?= $product['price'] ?>" required>

        <label for="image">Change Image (optional)</label>
        <input type="file" name="image" id="image" accept="image/*">

        <label class="checkbox-label">
            <input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>>
            Mark as Featured
        </label>
<br>
        <button type="submit">Update Product</button>
    </form>

    <a href="seller_products.php" class="back-link">← Back to Products</a>
</div>

</body>
</html>
