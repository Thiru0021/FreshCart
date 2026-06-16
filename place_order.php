<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['customer_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: shop.php");
    exit();
}

$user_id = intval($_SESSION['customer_id']);
$shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address']);

// Fallback logic check: If payment option isn't set, default it to COD
$payment_method = isset($_POST['payment_option']) ? mysqli_real_escape_string($conn, $_POST['payment_option']) : 'COD';

// 1. Compile subtotal values dynamically from database cart table to prevent client-side tampering
$calc_query = mysqli_query($conn, "SELECT c.qty, p.selling_price FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = $user_id");
$subtotal = 0;
while($c_row = mysqli_fetch_assoc($calc_query)) {
    $subtotal += ($c_row['selling_price'] * $c_row['qty']);
}
$grand_total = $subtotal + 45.00;

// 2. Insert order data securely into the updated database structure
$order_query = "INSERT INTO orders (customer_id, total_amount, delivery_address, payment_method, order_status) 
                VALUES ($user_id, $grand_total, '$shipping_address', '$payment_method', 'Pending')";
                
if (mysqli_query($conn, $order_query)) {
    $generated_order_id = mysqli_insert_id($conn);

    // 3. Snapshot items into order_items table before wiping the active cart
    $cart_snapshot_query = mysqli_query($conn, "SELECT c.qty, p.id, p.product_name, p.selling_price 
                                                 FROM cart c 
                                                 JOIN product p ON c.product_id = p.id 
                                                 WHERE c.user_id = $user_id");

    while ($item = mysqli_fetch_assoc($cart_snapshot_query)) {
        $p_id = intval($item['id']);
        $p_name = mysqli_real_escape_string($conn, $item['product_name']);
        $p_price = floatval($item['selling_price']);
        $p_qty = intval($item['qty']);

        // A. Insert into order_items archive
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_name, price, qty) 
                             VALUES ($generated_order_id, $p_id, '$p_name', $p_price, $p_qty)");

        // B. 🚀 INVENTORY CONTROL LAYER: Deduct ordered stock from products table
        // Note: Assumes your stock column inside the 'product' table is named exactly 'quantity'. 
        // If it is named 'qty' or 'product_qty' instead, change 'quantity' to match your column spelling!
        $deduct_stock_sql = "UPDATE product 
                             SET quantity = quantity - $p_qty 
                             WHERE id = $p_id";
                             
        $stock_update_status = mysqli_query($conn, $deduct_stock_sql);
        
        // Safety validation catch: If your column name is spelled wrong, this will alert you immediately!
        if (!$stock_update_status) {
            die("❌ STOCK DEDUCTION FAILED: " . mysqli_error($conn) . " | Query run: " . $deduct_stock_sql);
        }
    }

    // 4. Wipe active cart rows clean for this user
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

    // 5. Generate security token for invoice validation
    $_SESSION['one_time_bill_token'] = "VIEW_PASS_TOKEN_VAL_" . $generated_order_id;

    header("Location: invoice.php?order_id=" . $generated_order_id);
    exit();
} else {
    die("Order processing database failure: " . mysqli_error($conn));
}
?>