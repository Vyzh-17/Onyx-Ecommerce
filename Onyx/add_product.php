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
$message_type = "";

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
                    $message_type = "success";
                } else {
                    $message = "Database error: Could not add product.";
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Error moving uploaded file.";
                $message_type = "error";
            }
        } else {
            $message = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
            $message_type = "error";
        }
    } else {
        $message = "Please upload an image.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product | Seller Dashboard</title>
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

        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1em 2em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .logout {
            color: var(--danger);
        }

        .container {
            max-width: 800px;
            margin: 2em auto;
            padding: 0 1em;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray);
            font-weight: 400;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            border: 2px dashed var(--light-gray);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .file-upload-icon {
            font-size: 2rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .file-upload-text {
            color: var(--gray);
        }

        .file-upload-text strong {
            color: var(--primary);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-input {
            margin-right: 0.75rem;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .checkbox-label {
            color: var(--dark);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--gray);
            border: 1px solid var(--light-gray);
        }

        .btn-outline:hover {
            background-color: var(--light-gray);
            color: var(--dark);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .preview-container {
            display: none;
            margin-top: 1rem;
            text-align: center;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="seller_dashboard.php" class="navbar-brand">Seller Dashboard</a>
        <div class="navbar-nav">
            <a href="add_product.php" class="nav-link">
                <span class="material-icons">add</span>
                Add Product
            </a>
            <a href="pending.php" class="nav-link">
                <span class="material-icons">pending_actions</span>
                Orders
            </a>
            <a href="seller_profile.php" class="nav-link">
                <span class="material-icons">account_circle</span>
                Profile
            </a>
            <a href="logout.php" class="nav-link logout">
                <span class="material-icons">logout</span>
                Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Add New Product</h1>
            <p class="page-subtitle">Fill in the details below to list a new product</p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" enctype="multipart/form-data" action="add_product.php">
                <div class="form-group">
                    <label for="name" class="form-label">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter product name" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" placeholder="Enter detailed product description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Price ($)</label>
                    <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <div class="file-upload">
                        <input type="file" name="image" id="image" class="file-upload-input" accept=".jpg,.jpeg,.png,.gif" required>
                        <label for="image" class="file-upload-label">
                            <span class="material-icons file-upload-icon">cloud_upload</span>
                            <span class="file-upload-text">Click to upload or <strong>browse</strong> your files</span>
                            <span class="file-upload-text">Supports: JPG, PNG, GIF (Max 5MB)</span>
                        </label>
                    </div>
                    <div class="preview-container" id="previewContainer">
                        <img id="previewImage" class="preview-image" src="#" alt="Preview">
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="featured" id="featured" class="checkbox-input" value="1">
                    <label for="featured" class="checkbox-label">Feature this product on homepage</label>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <span class="material-icons" style="margin-right: 8px;">add_circle</span>
                        Add Product
                    </button>
                    <a href="seller_dashboard.php" class="btn btn-outline">
                        <span class="material-icons" style="margin-right: 8px;">close</span>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image preview functionality
        const imageInput = document.getElementById('image');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    previewImage.src = this.result;
                    previewContainer.style.display = 'block';
                });
                
                reader.readAsDataURL(file);
            }
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>