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
$seller_id = $_SESSION['user_id']; // Or any specific seller ID you're targeting

$stmt1 = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND seller_id = ?");
$stmt1->bind_param("i", $seller_id);
$stmt1->execute();
$featuredProducts = $stmt1->get_result();

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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
     <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color:rgb(255, 253, 253);
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
    #feature{
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap:wrap ;
    padding-left:10%;
    padding-right:10%;

}
#feature .fe-box{
    width: 180px;
    text-align: center;
    padding: 25px 15px;
    font-family: sans-serif;
}
  </style>
  
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
<br> <br>
<section id="feature"class="section-1">
            <div class="fe-box">
                <img src="de.jpg" width="200px" length="200px"alt=""style="padding-bottom: 20%;">
                <h6 style="padding-left: 49%;">Delivery By Us</h6>
            </div>
           

            <div class="fe-box">
                <img src="s.jpg" width="130px" length="130px"alt=""style="padding-bottom: 5%;">
                <h6>24/7 Support</h6>
            </div>
            <div class="fe-box">
                <img src="p.png" width="130px" length="130"alt=""style="padding-bottom: 10%;">
                <h6>Promotions</h6>
            </div>

            <div class="fe-box">
                <img src="u.png" width="120px" length="120px"alt="" style="padding-bottom: 20%;">
                <h6>Save Time</h6>
            </div>
        </section >

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
</div>
</body>
</html>
