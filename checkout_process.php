<?php
session_start();

// Check if the user is a guest
if (!isset($_SESSION['customer_id'])) {
    // Inject a persistent flag into memory so the login handler knows they were trying to checkout
    $_SESSION['redirect_to_checkout'] = true;
    
    // Redirect to the login page immediately
    header("Location: login.php");
    exit();
}

// If they are already logged in, pass them straight through to the checkout screen
header("Location: checkout.php");
exit();
?>