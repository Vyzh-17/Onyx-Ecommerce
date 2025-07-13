<?php
session_start();
include 'config.php'; // Database connection

// Ensure the seller is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: Unauthorized access. Please log in.");
}

$seller_id = $_SESSION['user_id']; // Logged-in seller's ID

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Ensure valid ID

    // Delete the product only if it belongs to the logged-in seller
    $sql = "DELETE FROM products WHERE id = ? AND seller_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $seller_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        echo "<script>
        alert('Product deleted successfully!');
        window.location.href='seller_products.php';
      </script>"; // Redirect after deletion
        exit();
    } else {
        echo "Error deleting product.";
    }
} else {
    echo "Invalid product ID.";
}
?>
