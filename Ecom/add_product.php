<?php
session_start();
include("config.php");

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit;
}
$seller_id = $_SESSION['user_id'];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $popularity = 0; // default popularity

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExt)) {
            // Generate a unique file name to prevent overwriting
            $newFileName = uniqid() . "." . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                // Insert product into DB
$stmt = $conn->prepare("INSERT INTO products (name, description, price, image, featured, popularity, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdssii", $name, $description, $price, $newFileName, $featured, $popularity, $seller_id);

                if ($stmt->execute()) {
                    $message = "Product added successfully!";
                } else {
                    $message = "Database error: Could not add product.";
                }
                $stmt->close();
            } else {
                $message = "Error moving uploaded file.";
            }
        } else {
            $message = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
        }
    } else {
        $message = "Please upload an image.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Product</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f7f7;
             margin: 0;
            
        }
        form {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 1.5em;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        input[type=text], input[type=number], textarea {
            width: 100%;
            padding: 0.6em;
            margin: 0.6em 0 1em 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
        }
        label {
            font-weight: 600;
        }
        input[type=checkbox] {
            margin-right: 0.5em;
        }
        button {
            background-color: #000;
            color: white;
            padding: 0.7em 1.2em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #333;
        }
        .message {
            text-align: center;
            margin-bottom: 1em;
            font-weight: 600;
            color: green;
        }
        .error {
            color: red;
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
<h2 style="text-align:center;">Add New Product</h2>

<?php if($message): ?>
    <p class="message <?php echo (strpos($message, 'error') !== false || strpos($message, 'Invalid') !== false) ? 'error' : '' ?>">
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="add_product.php">
    <label for="name">Product Name</label>
    <input type="text" name="name" id="name" required>

    <label for="description">Description</label>
    <textarea name="description" id="description" rows="4" required></textarea>

    <label for="price">Price </label>
    <input type="number" step="1" name="price" id="price" required>

    <label for="image">Product Image</label>
    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif" required>

    <label><br><br>
        <input type="checkbox" name="featured" value="1">
        Featured Product
    </label>
<br><br>
<div style="display:flex;">
    <button type="submit">Add Product</button>
    <div style="text-align: center; padding-left:20px;">
    <a href="seller_dashboard.php" style="display: inline-block; padding: 10px 20px; background: #555; color: white; text-decoration: none; border-radius: 5px;">
        Cancel
    </a>
</div>
</div>

</form>

</body>
</html>
