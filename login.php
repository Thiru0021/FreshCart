<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Check if your customers database table profile column is named exactly like this:
$id_column_field = 'customer_id'; 
$address_column_field = 'address'; 

$message = "";

if (isset($_POST['login_submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['c_email']);
    $password = $_POST['c_password'];

    // 1. READ: Find the row matching this unique email
    $query = "SELECT * FROM customers WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $customer = mysqli_fetch_assoc($result);
        
        // 2. Verify input against the secure database hash string
        if (password_verify($password, $customer['password']) || $password === $customer['password']) {
            
            // 3. Save identifier tokens into memory sessions
            $_SESSION['customer_id'] = intval($customer[$id_column_field]);
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_address'] = isset($customer[$address_column_field]) ? $customer[$address_column_field] : '';
            
            $user_id = intval($_SESSION['customer_id']);

            // ====================================================================
            // 🧠 STATE CONSOLIDATION ENGINE: Combines Guest Database Rows into Customer Cart
            // ====================================================================
            if (isset($_COOKIE['guest_browser_token'])) {
                $browser_token = mysqli_real_escape_string($conn, $_COOKIE['guest_browser_token']);
                
                // Fetch all matching unauthenticated guest products out of your session_cart table
                $guest_items_query = mysqli_query($conn, "SELECT product_id, qty FROM session_cart WHERE browser_token = '$browser_token'");
                
                if ($guest_items_query && mysqli_num_rows($guest_items_query) > 0) {
                    while ($guest_row = mysqli_fetch_assoc($guest_items_query)) {
                        $product_id = intval($guest_row['product_id']);
                        $guest_qty = intval($guest_row['qty']);
                        
                        // Fetch warehouse ceilings to respect total stock limits
                        $stock_query = mysqli_query($conn, "SELECT quantity FROM product WHERE id = $product_id");
                        $product_meta = mysqli_fetch_assoc($stock_query);
                        $max_stock = $product_meta ? intval($product_meta['quantity']) : 0;
                        
                        // Cross-check if this item is already sitting in their profile row inside your table
                        $check_db_cart = mysqli_query($conn, "SELECT id, qty FROM cart WHERE user_id = $user_id AND product_id = $product_id");
                        
                        if (mysqli_num_rows($check_db_cart) > 0) {
                            $cart_row = mysqli_fetch_assoc($check_db_cart);
                            $existing_db_qty = intval($cart_row['qty']);
                            
                            // Combine quantities together seamlessly
                            $combined_qty = $existing_db_qty + $guest_qty;
                            if ($combined_qty > $max_stock) {
                                $combined_qty = $max_stock; 
                            }
                            
                            mysqli_query($conn, "UPDATE cart SET qty = $combined_qty WHERE user_id = $user_id AND product_id = $product_id");
                        } else {
                            // First time item is being registered to this account profile row allocation
                            if ($guest_qty > $max_stock) {
                                $guest_qty = $max_stock;
                            }
                            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, $guest_qty)");
                        }
                    }
                    
                    // ====================================================================
                    // 🧹 AUTOMATED PURGE ENGINE: Wipe entries so they don't get stuck in DB!
                    // ====================================================================
                    mysqli_query($conn, "DELETE FROM session_cart WHERE browser_token = '$browser_token'");
                }
                
                // Expire the cookie since migration has cleared successfully
                setcookie('guest_browser_token', '', time() - 3600, "/");
            }

            // Just in case old system session variables are floating around, clean them too
            if (isset($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }

            // ====================================================================
            // 🎯 SMART REDIRECTION GATEWAY: Routes users cleanly post-login
            // ====================================================================
            if (isset($_SESSION['redirect_to_checkout']) && $_SESSION['redirect_to_checkout'] === true) {
                unset($_SESSION['redirect_to_checkout']);
                header("Location: checkout.php");
            } else {
                header("Location: shop.php");
            }
            exit();
            
        } else {
            $message = "<div class='alert error'>Incorrect password credential!</div>";
        }
    } else {
        $message = "<div class='alert error'>No account found with that email address.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    <style>
        .auth-container { max-width: 420px; margin: 100px auto; padding: 30px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .auth-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 8px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: border 0.2s; }
        .form-control:focus { border-color: #10b981; }
        .alert { padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .alert.error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body class="shop-body">

    <div class="auth-container">
        <h2 class="auth-title">Welcome Back</h2>
        <p style="color: #64748b; text-align: center; margin-bottom: 24px;">Sign in to access your shopping basket.</p>
        
        <?php echo $message; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="c_email" class="form-control" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="c_password" class="form-control" placeholder="Enter your account password" required>
            </div>
            <button type="submit" name="login_submit" class="shop-buy-btn" style="padding:14px; font-size:16px; border:none; width:100%; cursor:pointer;">Sign In</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #64748b;">
            Don't have an account? <a href="register.php" style="color: #10b981; font-weight: 600; text-decoration: none;">Register here</a>
        </p>
    </div>

</body>
</html>