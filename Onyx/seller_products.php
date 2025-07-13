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

// Fetch stats
$stats_stmt = $conn->prepare("SELECT 
    COUNT(p.id) as total_products,
    COUNT(o.id) as total_orders,
    COALESCE(SUM(o.quantity * p.price), 0) as total_revenue
    FROM products p
    LEFT JOIN orders o ON p.id = o.product_id
    WHERE p.seller_id = ?");
$stats_stmt->bind_param("i", $seller_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Dashboard | MyStore</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #e0e7ff;
      --secondary: #3f37c9;
      --accent: #f72585;
      --dark: #1a1a2e;
      --light: #f8f9fa;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --info: #4895ef;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
      line-height: 1.6;
    }

    .dashboard {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      padding: 1.5rem 0;
      position: fixed;
      height: 100vh;
      transition: all 0.3s;
    }

    .brand {
      display: flex;
      align-items: center;
      padding: 0 1.5rem 1.5rem;
      border-bottom: 1px solid var(--light-gray);
    }

    .brand-logo {
      font-size: 1.75rem;
      color: var(--primary);
      margin-right: 0.75rem;
    }

    .brand-name {
      font-weight: 700;
      font-size: 1.25rem;
    }

    .nav-menu {
      padding: 1.5rem;
    }

    .nav-title {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--gray);
      margin-bottom: 1rem;
    }

    .nav-list {
      list-style: none;
    }

    .nav-item {
      margin-bottom: 0.5rem;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      color: var(--gray);
      text-decoration: none;
      transition: all 0.2s;
    }

    .nav-link:hover, .nav-link.active {
      background-color: var(--primary-light);
      color: var(--primary);
    }

    .nav-link.active {
      font-weight: 500;
    }

    .nav-icon {
      margin-right: 0.75rem;
      font-size: 1.25rem;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      margin-left: 240px;
      padding: 2rem;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 1.75rem;
      font-weight: 600;
    }

    .user-profile {
      display: flex;
      align-items: center;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 0.75rem;
      color: var(--primary);
    }

    .user-name {
      font-weight: 500;
    }

    /* Stats Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      border-left: 4px solid var(--primary);
    }

    .stat-card.products {
      border-left-color: var(--info);
    }

    .stat-card.orders {
      border-left-color: var(--success);
    }

    .stat-card.revenue {
      border-left-color: var(--accent);
    }

    .stat-title {
      font-size: 0.875rem;
      color: var(--gray);
      margin-bottom: 0.5rem;
    }

    .stat-value {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stat-change {
      font-size: 0.875rem;
      color: var(--success);
      display: flex;
      align-items: center;
    }

    /* Products Table */
    .card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      padding: 1.5rem;
      margin-bottom: 2rem;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .card-title {
      font-size: 1.25rem;
      font-weight: 600;
    }

    .btn {
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: var(--secondary);
    }

    .btn-icon {
      margin-right: 0.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background-color: var(--light-gray);
    }

    th {
      padding: 1rem;
      text-align: left;
      font-weight: 500;
      color: var(--gray);
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.05em;
    }

    td {
      padding: 1rem;
      border-bottom: 1px solid var(--light-gray);
    }

    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }

    .badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .badge-success {
      background-color: #d1fae5;
      color: #065f46;
    }

    .badge-warning {
      background-color: #fef3c7;
      color: #92400e;
    }

    .badge-danger {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .actions {
      display: flex;
      gap: 0.5rem;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: var(--light-gray);
      color: var(--gray);
      transition: all 0.2s;
      cursor: pointer;
      text-decoration: none;
    }

    .action-btn:hover {
      background-color: var(--primary-light);
      color: var(--primary);
    }

    .action-btn.delete:hover {
      background-color: #fee2e2;
      color: var(--danger);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 80px;
        padding: 1rem 0;
      }
      
      .brand-name, .nav-title, .nav-text {
        display: none;
      }
      
      .nav-link {
        justify-content: center;
        padding: 0.75rem;
      }
      
      .nav-icon {
        margin-right: 0;
        font-size: 1.5rem;
      }
      
      .main-content {
        margin-left: 80px;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <span class="brand-logo material-icons">store</span>
        <span class="brand-name">Onyx.</span>
      </div>
      
      <nav class="nav-menu">
        <h3 class="nav-title">Main</h3>
        <ul class="nav-list">
          <li class="nav-item">
            <a href="seller_dashboard.php" class="nav-link active">
              <span class="nav-icon material-icons">dashboard</span>
              <span class="nav-text">Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="seller_products.php" class="nav-link">
              <span class="nav-icon material-icons">inventory_2</span>
              <span class="nav-text">Products</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="pending.php" class="nav-link">
              <span class="nav-icon material-icons">receipt</span>
              <span class="nav-text">Orders</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="add_product.php" class="nav-link">
              <span class="nav-icon material-icons">add_circle</span>
              <span class="nav-text">Add Product</span>
            </a>
          </li>
        </ul>
        
        <h3 class="nav-title">Account</h3>
        <ul class="nav-list">
          <li class="nav-item">
            <a href="seller_profile.php" class="nav-link">
              <span class="nav-icon material-icons">account_circle</span>
              <span class="nav-text">Profile</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <span class="nav-icon material-icons">logout</span>
              <span class="nav-text">Logout</span>
            </a>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="header">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="user-profile">
          
         
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card products">
          <h3 class="stat-title">Total Products</h3>
          <p class="stat-value"><?php echo $stats['total_products'] ?? 0; ?></p>
          <span class="stat-change">
            <span class="material-icons">trending_up</span>
            12% from last month
          </span>
        </div>
        
        <div class="stat-card orders">
          <h3 class="stat-title">Total Orders</h3>
          <p class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></p>
          <span class="stat-change">
            <span class="material-icons">trending_up</span>
            8% from last month
          </span>
        </div>
        
        <div class="stat-card revenue">
          <h3 class="stat-title">Total Revenue</h3>
          <p class="stat-value">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
          <span class="stat-change">
            <span class="material-icons">trending_up</span>
            15% from last month
          </span>
        </div>
      </div>

      <!-- Products Table -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Your Products</h2>
          <a href="add_product.php" class="btn btn-primary">
            <span class="btn-icon material-icons">add</span>
            Add Product
          </a>
        </div>
        
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>Description</th>
              <th>Price</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $products->fetch_assoc()): ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 1rem;">
                  <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="product-img">
                  <span><?php echo htmlspecialchars($row['name']); ?></span>
                </div>
              </td>
              <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
              <td>$<?php echo htmlspecialchars(number_format($row['price'], 2)); ?></td>
              <td>
                <span class="badge badge-success">Instock</span>
              </td>
              <td>
                <div class="actions">
                  <a href="productviews.php?id=<?php echo $row['id']; ?>" class="action-btn" title="View">
                    <span class="material-icons">visibility</span>
                  </a>
                  <a href="editproduct.php?id=<?php echo $row['id']; ?>" class="action-btn" title="Edit">
                    <span class="material-icons">edit</span>
                  </a>
                  <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this product?');">
                    <span class="material-icons">delete</span>
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>