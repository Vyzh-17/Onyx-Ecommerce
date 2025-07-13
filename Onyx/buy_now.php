<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];

    // Here, redirect to a checkout page with product ID in query string
    header("Location: checkout.php?product_id=$product_id");
    exit;
}
?>
