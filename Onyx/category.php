<?php
include("config.php");

// List of valid categories
$valid_categories = ['electronics', 'fashion', 'home'];

// Get category from URL
if (!isset($_GET['category']) || !in_array($_GET['category'], $valid_categories)) {
    header("Location: index.php");
    exit();
}

$category_slug = $_GET['category'];

// Get category name
$category_names = [
    'electronics' => 'Electronics',
    'fashion' => 'Fashion',
    'home' => 'Home'
];

$category_name = $category_names[$category_slug];

// Get products in this category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = 
                        (SELECT id FROM categories WHERE slug = ?)");
$stmt->bind_param("s", $category_slug);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> | Onyx</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,1..1,-50..200" >
    <style>
        :root {
            --primary: #3a0ca3;
            --primary-light: #4361ee;
            --secondary: #f72585;
            --dark: #14213d;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f7;
            color: #333;
            line-height: 1.6;
        }

        .category-header {
            text-align: center;
            padding: 3rem 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            margin-bottom: 2rem;
        }

        .category-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .category-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 0 2rem 4rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .product-image-container {
            height: 240px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fafafa;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
            padding: 1rem;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3rem;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.75rem 0;
        }

        .view-btn {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .view-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            box-shadow: 0 5px 15px rgba(58, 12, 163, 0.3);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            font-size: 1.2rem;
            grid-column: 1 / -1;
        }

        .no-results-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .category-breadcrumb {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            font-size: 0.9rem;
        }

        .category-breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .category-breadcrumb a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                padding: 0 1rem 2rem;
                gap: 1.5rem;
            }
            
            .category-header h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .category-header {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="category-breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <span><?php echo htmlspecialchars($category_name); ?></span>
    </div>

    <div class="category-header">
        <h1><?php echo htmlspecialchars($category_name); ?></h1>
        <p><?php echo $products->num_rows; ?> products available</p>
    </div>

    <div class="product-grid">
        <?php if ($products->num_rows > 0): ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price">$<?php echo htmlspecialchars($product['price']); ?></div>
                        <a href="view_product.php?id=<?php echo $product['id']; ?>" class="view-btn">
                            View Product
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
               
                <p>No products found in this category yet.</p>
                <a href="index.php" class="view-btn" style="display: inline-block; width: auto; padding: 0.75rem 2rem; margin-top: 1.5rem;">
                    Browse All Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>