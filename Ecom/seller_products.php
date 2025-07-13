<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '1') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch products by this seller
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Seller Dashboard</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color: #f5f5f5;
    }

    .navbar {
      background-color: #222;
      color: white;
      padding: 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      margin-left: 15px;
    }

    .container {
      max-width: 1000px;
      margin: 2em auto;
      padding: 1em;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 1em;
    }

    .add-product {
      text-align: right;
      margin-bottom: 1em;
    }

    .add-product a {
      background-color: black;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 8px;
    }

    .add-product a:hover {
      background-color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th, table td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }

    table th {
      background-color: #f0f0f0;
    }

    .actions a {
      margin-right: 10px;
      text-decoration: none;
      color: #007bff;
    }

    .actions a:hover {
      text-decoration: underline;
    }

    .logout {
      color: red;
      text-decoration: underline;
    }
  </style>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
     <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
</head>
<body>

<div class="navbar">
  <div><strong>Seller Dashboard</strong></div>
  <div>
    <a href="add_product.php" class="logout">add</a>
      <a href="pending.php" class="logout">pending</a>
    <a href="seller_products.php" class="logout">products</a>
    <a href="seller_profile.php" class="logout"><span class="material-icons">
account_circle
</span></a>
    <a href="logout.php" class="logout"><span class="material-symbols-outlined">
logout
</span></a>
  </div>
</div>

<div class="container">
  <h2>📦 Your Products</h2>

  <div class="add-product">
   
  </div>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price ($)</th>
        <th>Image</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $products->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['description']); ?></td>
          <td><?php echo htmlspecialchars($row['price']); ?></td>
          <td><img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" width="60" height="60"></td>
          <td class="actions">
            <a class="button view" href="productviews.php?id=<?php echo $row['id']; ?>">View</a>

            <a href="editproduct.php?id=<?php echo $row['id']; ?>">Edit</a>
            <a href="delete_product.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this product?');">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
