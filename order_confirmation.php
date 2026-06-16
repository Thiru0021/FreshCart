<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!isset($_SESSION['active_order_id'])) {
    header("Location: shop.php");
    exit();
}

$order_id = $_SESSION['active_order_id'];
$msg = "";

// 3. Handle final address collection submission
if (isset($_POST['finalize_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $payment = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // UPDATE: Update order record with real routing particulars and switch status to 'Pending'
    $update_order = "UPDATE orders 
                     SET delivery_address = '$address', 
                         order_status = 'Pending' 
                     WHERE order_id = $order_id";
                     
    if (mysqli_query($conn, $update_order)) {
        unset($_SESSION['active_order_id']); // Terminate confirmation workflow token
        $msg = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Order - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    <style>
        .confirm-box { max-width: 550px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
        .radio-group { display: flex; gap: 20px; margin-bottom: 25px; }
        .radio-box { border: 1px solid #cbd5e1; padding: 15px; border-radius: 8px; flex: 1; cursor: pointer; display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: sans-serif;">

    <div class="confirm-box">
        <?php if ($msg == "success"): ?>
            <div style="text-align: center; padding: 20px;">
                <h2 style="color: #10b981;">🎉 Order Confirmed Successfully!</h2>
                <p style="color: #64748b; margin: 10px 0 25px; size: 10px;" >Your stock items are reserved, and our logistics team is packing your crate.</p>
                <a href="shop.php" class="shop-filter-pill active" style="text-decoration: none; padding: 12px 30px;">Return to Shop</a>
            </div>
        <?php else: ?>
            <h2>📍 Delivery & Payment Details</h2>
            <p style="color: #64748b; margin-bottom: 25px;">Please specify your current shipping address routing rules below to finalize Order #<?php echo $order_id; ?>.</p>
            
            <form action="order_confirmation.php" method="POST">
                <label style="font-weight: 600; display: block; margin-bottom: 8px;">Delivery Destination Address</label>
                <textarea name="delivery_address" class="form-control" rows="4" placeholder="Type full delivery address location details here..." required></textarea>
                
                <label style="font-weight: 600; display: block; margin-bottom: 12px;">Bill Payment Method</label>
                <div class="radio-group">
                    <label class="radio-box">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <span>💵 Cash on Delivery</span>
                    </label>
                    <label class="radio-box">
                        <input type="radio" name="payment_method" value="UPI">
                        <span>📱 UPI / Online Scan</span>
                    </label>
                </div>
                
                <button type="submit" name="finalize_order" class="shop-buy-btn" style="padding: 14px;">Confirm & Finalize Crate Shipment</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>