<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != '1') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND EXISTS (SELECT 1 FROM products WHERE id = orders.product_id AND seller_id = ?)");
    $stmt->bind_param("sii", $new_status, $order_id, $seller_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Order status updated to $new_status!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating order status: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    header("Location: pending.php");
    exit();
}

// Fetch orders for this seller
$query = "SELECT o.id, o.order_date, o.quantity, o.status, o.address, o.phone, 
                 o.payment_method, o.name as buyer_name, 
                 p.name as product_name, p.price, p.image, (o.quantity * p.price) as total_price
          FROM orders o
          JOIN products p ON o.product_id = p.id
          WHERE p.seller_id = ?
          ORDER BY FIELD(o.status, 'Pending', 'Processing', 'Shipped', 'Delivered'), o.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Seller Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4e73df;
            --primary-light: #e0e7ff;
            --secondary: #3a56a7;
            --accent: #f72585;
            --dark: #2a2a3c;
            --light: #f8f9fa;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #5a5c69;
        }
        
        .sidebar {
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            min-height: 100vh;
        }
        
        .sidebar .nav-link {
            color: #3a56a7;
            font-weight: 600;
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
        }
        
        .sidebar .nav-link.active {
            color: var(--primary);
            background-color: rgba(78, 115, 223, 0.1);
            border-left: 3px solid var(--primary);
        }
        
        .sidebar .nav-link:hover:not(.active) {
            color:rgb(0, 0, 0);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-heading {
            color:rgb(0, 0, 0);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.13em;
            padding: 1.5rem 1rem 0.5rem;
        }
        
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #f8f4e5;
            color: #946c00;
            border: 1px solid #ffdf7e;
        }
        
        .status-processing {
            background-color: #e6f3ff;
            color: #0061f2;
            border: 1px solid #9ec5fe;
        }
        
        .status-shipped {
            background-color: #e6f9f0;
            color: #008a47;
            border: 1px solid #84e8b5;
        }
        
        .status-delivered {
            background-color: #f0f7ff;
            color: #6900c7;
            border: 1px solid #c8a2ff;
        }
        
        .order-card {
            transition: all 0.3s ease;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
            border-color: var(--primary);
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }
        
        .progress-tracker {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 25px 0;
        }
        
        .progress-tracker::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 4px;
            background: #e3e6f0;
            z-index: 1;
            transform: translateY(-50%);
            border-radius: 2px;
        }
        
    .progress-tracker::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    width: <?php 
        if (isset($order)) {
            if ($order['status'] == 'Delivered') {
                echo '100%';
            } elseif ($order['status'] == 'Shipped') {
                echo '66%';
            } elseif ($order['status'] == 'Processing') {
                echo '33%';
            } else {
                echo '0%';
            }
        } else {
            echo '0%';
        }
    ?>;
    height: 4px;
    background: var(--primary);
    z-index: 2;
    transform: translateY(-50%);
    transition: width 0.5s ease;
    border-radius: 2px;
}
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 3;
            position: relative;
        }
        
        .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e3e6f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            color: #b7b9cc;
            font-size: 1rem;
            border: 3px solid white;
        }
        
        .step-icon.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.2);
        }
        
        .step-label {
            font-size: 0.8rem;
            color: #b7b9cc;
            font-weight: 600;
            text-align: center;
        }
        
        .step-label.active {
            color: var(--primary);
        }
        
        .action-btn {
            transition: all 0.2s;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 0.3rem;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-weight: 700;
            color: var(--dark);
        }
        
        .main-content {
            background-color: #f8f9fc;
        }
        
        .total-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .order-details p {
            margin-bottom: 0.4rem;
        }
        
        .order-details strong {
            color: #4a4b65;
        }
        
        .alert {
            border-radius: 0.35rem;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .page-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .page-header h2 {
            font-weight: 700;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar py-4">
                <div class="sidebar-sticky">
                    <div class="text-center mb-4">
                        <h4 class="text-primary fw-bold"><i class="bi bi-shop me-2"></i>Seller Dashboard</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="seller_dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                     
                        <li class="nav-item">
                            <a class="nav-link active" href="seller_orders.php">
                                <i class="bi bi-cart-check me-2"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_product.php">
                                <i class="bi bi-plus-circle me-2"></i> Add Product
                            </a>
                        </li>
                        <hr class="my-2 text-muted">
                        <li class="nav-item">
                            <a class="nav-link" href="seller_profile.php">
                                <i class="bi bi-person-circle me-2"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-cart-check me-2"></i>Manage Orders</h2>
                        
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                        <i class="bi <?php echo $_SESSION['message_type'] == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <div class="row">
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card order-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">Order #<?php echo $order['id']; ?></h5>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></small>
                                        </div>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <i class="bi <?php 
                                                echo $order['status'] == 'Pending' ? 'bi-hourglass' : 
                                                ($order['status'] == 'Processing' ? 'bi-gear' : 
                                                ($order['status'] == 'Shipped' ? 'bi-truck' : 'bi-check-circle')); 
                                            ?> me-1"></i>
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex mb-3">
                                        <img src="uploads/<?php echo htmlspecialchars($order['image']); ?>" class="product-img me-3" alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                        <div>
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                            <p class="mb-1 text-muted">Quantity: <?php echo $order['quantity']; ?></p>
                                            <p class="mb-0 text-muted">$<?php echo number_format($order['price'], 2); ?> each</p>
                                        </div>
                                    </div>
                                    
                                    <div class="order-details mb-3 p-3 bg-light rounded">
                                        <p class="mb-2"><i class="bi bi-person me-2"></i><strong>Buyer:</strong> <?php echo htmlspecialchars($order['buyer_name']); ?></p>
                                        <p class="mb-2"><i class="bi bi-geo-alt me-2"></i><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                        <p class="mb-2"><i class="bi bi-telephone me-2"></i><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                        <p class="mb-0"><i class="bi bi-credit-card me-2"></i><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                    </div>
                                    
                                    <!-- Order Progress Tracker -->
                                    <div class="progress-tracker mb-3">
                                        <div class="progress-step">
                                            <div class="step-icon <?php echo in_array($order['status'], ['pending', 'Processing', 'Shipped', 'Delivered']) ? 'active' : ''; ?>">
                                                <i class="bi bi-hourglass"></i>
                                            </div>
                                            <span class="step-label <?php echo in_array($order['status'], ['pending', 'Processing', 'Shipped', 'Delivered']) ? 'active' : ''; ?>">Pending</span>
                                        </div>
                                        <div class="progress-step">
                                            <div class="step-icon <?php echo in_array($order['status'], ['Processing', 'Shipped', 'Delivered']) ? 'active' : ''; ?>">
                                                <i class="bi bi-gear"></i>
                                            </div>
                                            <span class="step-label <?php echo in_array($order['status'], ['Processing', 'Shipped', 'Delivered']) ? 'active' : ''; ?>">Processing</span>
                                        </div>
                                        <div class="progress-step">
                                            <div class="step-icon <?php echo in_array($order['status'], ['Shipped', 'Delivered']) ? 'active' : ''; ?>">
                                                <i class="bi bi-truck"></i>
                                            </div>
                                            <span class="step-label <?php echo in_array($order['status'], ['Shipped', 'Delivered']) ? 'active' : ''; ?>">Shipped</span>
                                        </div>
                                        <div class="progress-step">
                                            <div class="step-icon <?php echo $order['status'] == 'Delivered' ? 'active' : ''; ?>">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <span class="step-label <?php echo $order['status'] == 'Delivered' ? 'active' : ''; ?>">Delivered</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="total-price mb-0">Total: $<?php echo number_format($order['total_price'], 2); ?></h5>
                                        
                                        <div class="d-flex">
                                            <?php if ($order['status'] == 'pending'): ?>
                                                <form method="POST" class="me-2">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="Processing">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary action-btn">
                                                        <i class="bi bi-play-fill me-1"></i> Process
                                                    </button>
                                                </form>
                                            <?php elseif ($order['status'] == 'Processing'): ?>
                                                <form method="POST" class="me-2">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="Shipped">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-success action-btn">
                                                        <i class="bi bi-truck me-1"></i> Ship
                                                    </button>
                                                </form>
                                            <?php elseif ($order['status'] == 'Shipped'): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <input type="hidden" name="status" value="Delivered">
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-success action-btn">
                                                        <i class="bi bi-check-circle me-1"></i> Deliver
                                                    </button>
                                                </form>
                                            <?php elseif ($order['status'] == 'Delivered'): ?>
                                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Completed</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">No Orders Found</h5>
                                    <p class="mb-0">You don't have any orders for your products yet.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
        
        // Update progress bar based on status
        function updateProgressBar(status) {
            const progressBar = document.querySelector('.progress-tracker::after');
            if (!progressBar) return;
            
            let width = '0%';
            switch(status) {
                case 'Processing': width = '33%'; break;
                case 'Shipped': width = '66%'; break;
                case 'Delivered': width = '100%'; break;
                default: width = '0%';
            }
            
            progressBar.style.width = width;
        }
        
        // Call this function when page loads for each order card
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.order-card').forEach(card => {
                const status = card.querySelector('.status-badge').textContent.trim();
                updateProgressBar(status);
            });
        });
    </script>
</body>
</html>