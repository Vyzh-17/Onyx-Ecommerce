<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id']) || !isset($_GET['cart_id'])) {
    header("Location: login.php");
    exit;
}

$cart_id = intval($_GET['cart_id']);
$stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();

header("Location: cart.php");
exit;
?>
