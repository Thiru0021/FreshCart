<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database failure']);
    exit();
}

if (isset($_POST['product_id']) && isset($_POST['action'])) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];

    // Pull warehouse ceiling metrics to verify available product constraints
    $stock_query = mysqli_query($conn, "SELECT quantity FROM product WHERE id = $product_id");
    $product_data = mysqli_fetch_assoc($stock_query);
    $max_stock = $product_data ? intval($product_data['quantity']) : 0;

    if (isset($_SESSION['customer_id'])) {
        // =========================================================
        // 1. AUTHENTICATED CUSTOMER STATE: Mutate MySQL Table Rows
        // ========================================================= 
        $user_id = intval($_SESSION['customer_id']);

        if ($action == 'add') {
            $check_cart = mysqli_query($conn, "SELECT qty FROM cart WHERE user_id = $user_id AND product_id = $product_id");
            if ($cart_row = mysqli_fetch_assoc($check_cart)) {
                if (intval($cart_row['qty']) < $max_stock) {
                    mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE user_id = $user_id AND product_id = $product_id");
                }
            } else {
                mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) VALUES ($user_id, $product_id, 1)");
            }
        } 
        else if ($action == 'remove') {
            mysqli_query($conn, "UPDATE cart SET qty = qty - 1 WHERE user_id = $user_id AND product_id = $product_id");
            mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id AND qty <= 0");
        }

        // Gather real-time numbers back from the database table to update the browser UI
        $qty_res = mysqli_query($conn, "SELECT qty FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        $new_qty = ($qty_row = mysqli_fetch_assoc($qty_res)) ? intval($qty_row['qty']) : 0;

        $count_res = mysqli_query($conn, "SELECT COUNT(*) as unique_items FROM cart WHERE user_id = $user_id");
        $count_row = mysqli_fetch_assoc($count_res);
        $unique_items_count = intval($count_row['unique_items']);
    } 
    else {
        // =========================================================
        // 2. 🚀 UPDATED ANONYMOUS GUEST STATE: Persistent Database Session Cart
        // =========================================================
        // Check for or initialize a long-lived cookie token (lasts for 30 days)
        if (!isset($_COOKIE['guest_browser_token'])) {
            $token_seed = uniqid('guest_', true);
            setcookie('guest_browser_token', $token_seed, time() + (86400 * 30), "/");
            $_COOKIE['guest_browser_token'] = $token_seed;
        }

        $browser_token = mysqli_real_escape_string($conn, $_COOKIE['guest_browser_token']);

        if ($action == 'add') {
            $check_guest_cart = mysqli_query($conn, "SELECT id, qty FROM session_cart WHERE browser_token = '$browser_token' AND product_id = $product_id");
            
            if ($guest_row = mysqli_fetch_assoc($check_guest_cart)) {
                if (intval($guest_row['qty']) < $max_stock) {
                    mysqli_query($conn, "UPDATE session_cart SET qty = qty + 1 WHERE browser_token = '$browser_token' AND product_id = $product_id");
                }
            } else {
                // First insert a draft tracking record
                mysqli_query($conn, "INSERT INTO session_cart (session_id, browser_token, product_id, qty) VALUES ('PENDING', '$browser_token', $product_id, 1)");
                $new_row_id = mysqli_insert_id($conn);
                
                // Formulate the customized auto-increment identifier structure (e.g., ss001)
                $custom_session_str = "ss" . sprintf("%03d", $new_row_id);
                
                // Finalize the record with the generated identifier
                mysqli_query($conn, "UPDATE session_cart SET session_id = '$custom_session_str' WHERE id = $new_row_id");
            }
        } 
        else if ($action == 'remove') {
            mysqli_query($conn, "UPDATE session_cart SET qty = qty - 1 WHERE browser_token = '$browser_token' AND product_id = $product_id");
            mysqli_query($conn, "DELETE FROM session_cart WHERE browser_token = '$browser_token' AND qty <= 0");
        }

        // Fetch metrics back out of session_cart to update the interface asynchronously
        $qty_res = mysqli_query($conn, "SELECT qty FROM session_cart WHERE browser_token = '$browser_token' AND product_id = $product_id");
        $new_qty = ($qty_row = mysqli_fetch_assoc($qty_res)) ? intval($qty_row['qty']) : 0;

        $count_res = mysqli_query($conn, "SELECT COUNT(*) as unique_items FROM session_cart WHERE browser_token = '$browser_token'");
        $count_row = mysqli_fetch_assoc($count_res);
        $unique_items_count = intval($count_row['unique_items']);
    }

    // Return the final data payload as a clean JSON package
    echo json_encode([
        'status' => 'success',
        'new_quantity' => $new_qty,
        'unique_items_count' => $unique_items_count
    ]);
    exit();
}
?>