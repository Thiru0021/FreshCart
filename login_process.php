<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the core database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("❌ DATABASE CONNECTION FAILED: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("❌ ERROR: Method must be POST.");
}

// Sanitize user inputs
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password']; 

// ====================================================================
// 🔍 FIXED TABLE TARGET: Changed from 'users' to 'customers'
// ====================================================================
$user_query = mysqli_query($conn, "SELECT * FROM customers WHERE email = '$email'");

if (!$user_query) {
    die("❌ SQL CUSTOMER QUERY FAILED: " . mysqli_error($conn) . "<br>Verify that your 'customers' table contains an 'email' column.");
}

if (mysqli_num_rows($user_query) > 0) {
    $user = mysqli_fetch_assoc($user_query);
    
    // Check if passwords match (Adjust if you use md5 or password_hash encryption)
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        
        // Auto-detect primary key ID field naming from your 'customers' table structure
        // Checks if column name is 'id' or 'customer_id' or 'user_id'
        $detected_id = null;
        if (isset($user['id'])) { $detected_id = $user['id']; }
        elseif (isset($user['customer_id'])) { $detected_id = $user['customer_id']; }
        elseif (isset($user['user_id'])) { $detected_id = $user['user_id']; }
        
        if ($detected_id === null) {
            die("❌ CONFIGURATION ERROR: Could not find an identifying ID column ('id' or 'customer_id') inside your 'customers' table.");
        }

        // Set global customer identity session variables
        $_SESSION['customer_id'] = intval($detected_id);
        $_SESSION['customer_name'] = isset($user['name']) ? $user['name'] : $user['username'];
        $_SESSION['customer_email'] = $user['email'];
        $_SESSION['customer_address'] = isset($user['address']) ? $user['address'] : '';

        echo "🟢 LOGIN SUCCESSFUL! Customer ID registered in session: " . $_SESSION['customer_id'] . "<br>";

        // ====================================================================
        // 🧠 STATE CONSOLIDATION ENGINE: Combines Guest & DB Cart Items (1 + 2 = 3)
        // ====================================================================
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            echo "📦 Temporary guest cart detected. Merging items into database table...<br>";
            
            foreach ($_SESSION['cart'] as $product_id => $guest_qty) {
                $product_id = intval($product_id);
                $guest_qty = intval($guest_qty);
                $user_id = intval($_SESSION['customer_id']);

                // Fetch total items available in store warehouse inventory
                $stock_query = mysqli_query($conn, "SELECT quantity FROM product WHERE id = $product_id");
                $product_meta = mysqli_fetch_assoc($stock_query);
                $max_stock = $product_meta ? intval($product_meta['quantity']) : 0;

                // Check if this item is already sitting in their account table row inside phpMyAdmin
                $check_db_cart = mysqli_query($conn, "SELECT id, qty FROM cart WHERE user_id = $user_id AND product_id = $product_id");
                
                if (!$check_db_cart) {
                    die("❌ SQL CRASH: Cart table query failed. Error: " . mysqli_error($conn) . "<br>Verify that your 'cart' table contains columns named exactly: 'user_id', 'product_id', and 'qty'.");
                }

                if (mysqli_num_rows($check_db_cart) > 0) {
                    $cart_row = mysqli_fetch_assoc($check_db_cart);
                    $existing_db_qty = intval($cart_row['qty']);
                    
                    // Combine metrics (1 + 2 = 3)
                    $combined_qty = $existing_db_qty + $guest_qty;
                    if ($combined_qty > $max_stock) {
                        $combined_qty = $max_stock; 
                    }
                    
                    $update_status = mysqli_query($conn, "UPDATE cart SET qty = $combined_qty WHERE user_id = $user_id AND product_id = $product_id");
                    if (!$update_status) {
                        die("❌ SQL UPDATE ROW FAILED: " . mysqli_error($conn));
                    }
                    echo "🔄 Combined product ID $product_id. New Total Qty: $combined_qty<br>";
                } else {
                    // First time item is being registered to this account profile row
                    if ($guest_qty > $max_stock) {
                        $guest_qty = $max_stock;
                    }
                    $insert_status = mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, $guest_qty)");
                    if (!$insert_status) {
                        die("❌ SQL INSERT ROW FAILED: " . mysqli_error($conn));
                    }
                    echo "📥 Inserted product ID $product_id with Qty: $guest_qty as a fresh database record row.<br>";
                }
            }
            
            // Clear out temporary guest tray arrays out of browser session cache
            unset($_SESSION['cart']);
            echo "🧹 Session cart memory swept clean.<br>";
        } else {
            echo "ℹ️ Guest cart was empty. No data migration needed.<br>";
        }

        // ====================================================================
        // 🎯 REDIRECTION ROUTER
        // ====================================================================
        if (isset($_SESSION['redirect_to_checkout']) && $_SESSION['redirect_to_checkout'] === true) {
            unset($_SESSION['redirect_to_checkout']);
            echo "🚀 Intent flagged. Forwarding directly to checkout page in 1.5 seconds...";
            echo "<script>setTimeout(function(){ window.location.href='checkout.php'; }, 1500);</script>";
        } else {
            echo "🏠 Forwarding straight to store catalog in 1.5 seconds...";
            echo "<script>setTimeout(function(){ window.location.href='shop.php'; }, 1500);</script>";
        }
        exit();

    } else {
        header("Location: login.php?error=wrong_password");
        exit();
    }
} else {
    header("Location: login.php?error=user_not_found");
    exit();
}
?>