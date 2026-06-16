<?php
session_start();

// Connect to the core database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Ensure an order ID parameter is present in the URL string
if (!isset($_GET['order_id'])) {
    header("Location: shop.php");
    exit();
}

$target_order_id = intval($_GET['order_id']);

// ====================================================================
// 🛡️ SECURITY PROTOCOL PROTECTION WALL (Anti-ID Enumeration Firewall)
// ====================================================================
if (!isset($_SESSION['one_time_bill_token']) || $_SESSION['one_time_bill_token'] !== "VIEW_PASS_TOKEN_VAL_" . $target_order_id) {
    echo "<div style='text-align:center; padding:100px 20px; font-family:sans-serif; background:#f8fafc; min-height:100vh; box-sizing:border-box;'>
            <div style='max-width:500px; margin:0 auto; background:white; padding:40px; border-radius:12px; border:1px solid #e2e8f0;'>
                <h2 style='color:#ef4444; margin-top:0;'>⚠️ Secure Document Access Expired</h2>
                <p style='color:#64748b; line-height:22px; font-size:15px;'>For privacy, data isolation, and security compliance, customer invoices can only be processed once immediately upon order completion.</p>
                <a href='shop.php' style='display:inline-block; margin-top:15px; padding:12px 24px; background:#10b981; color:white; text-decoration:none; font-weight:700; border-radius:8px;'>Go to Store Homepage</a>
            </div>
          </div>";
    exit();
}

// --- CRITICAL DESTRUCT SEQUENCE ---
unset($_SESSION['one_time_bill_token']);

// 1. Fetch main metadata parameters for the order row from phpMyAdmin orders table
$order_data_query = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = $target_order_id");
$order_meta = mysqli_fetch_assoc($order_data_query);

if (!$order_meta) {
    die("Error: Order data could not be retrieved.");
}

// 2. Fetch line items linked directly to this transaction record order serial number
$items_query = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $target_order_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Statement #<?php echo $target_order_id; ?></title>
    <style>
        body { font-family: 'Courier New', monospace; background: #f8fafc; padding: 40px; color: #0f172a; margin: 0; }
        .invoice-box { max-width: 650px; margin: 0 auto; background: white; padding: 40px; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.02); border-radius: 8px; }
        .invoice-header { text-align: center; border-bottom: 2px dashed #0f172a; padding-bottom: 15px; margin-top: 0; margin-bottom: 25px; }
        .meta-table { width: 100%; font-size: 14px; margin-bottom: 25px; line-height: 22px; }
        .address-block { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; font-size: 14px; line-height: 22px; margin-bottom: 25px; }
        
        /* 📊 LINE-ITEM BREAKDOWN TABLE STYLING */
        .item-breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 14px; }
        .item-breakdown-table th { padding: 10px; background: #f8fafc; text-align: left; font-weight: bold; border-bottom: 2px solid #0f172a; border-top: 1px solid #e2e8f0; }
        .item-breakdown-table td { padding: 12px 10px; border-bottom: 1px dashed #cbd5e1; text-align: left; }
        
        .home-btn-link { display: inline-block; padding: 14px 35px; background-color: #10b981; color: white; text-decoration: none; font-weight: 700; border-radius: 8px; font-size: 15px; transition: background 0.2s ease; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15); }
        .home-btn-link:hover { background-color: #059669; }
        
        @media print {
            body { background: white; padding: 0; }
            .invoice-box { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .home-btn-link { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    
    <div class="invoice-header">
        <h2 style="margin: 0 0 5px 0; font-weight: 800; letter-spacing: 1px;">FRESHCART GROCERY STORE</h2>
        <span style="font-size: 13px; color: #64748b; font-weight: bold;">OFFICIAL TRANSACTION STATEMENT</span>
    </div>
    
    <table class="meta-table">
        <tr>
            <td><strong>Invoice Serial:</strong> FC-ORD-<?php echo $target_order_id; ?></td>
            <td style="text-align: right;"><strong>Timestamp:</strong> <?php echo date("Y-m-d H:i"); ?></td>
        </tr>
        <tr>
            <td><strong>Customer ID:</strong> Profile Row #<?php echo htmlspecialchars($order_meta['customer_id']); ?></td>
            <td style="text-align: right; color: #16a34a; font-weight: bold;">Status: Order Placed Successfully</td>
        </tr>
    </table>

    <div class="address-block">
        <strong style="color: #1e293b; display: block; margin-bottom: 6px;">📍 Shipping Drop Destination:</strong>
        <span style="color: #475569;">
            <?php echo nl2br(htmlspecialchars($order_meta['delivery_address'])); ?>
        </span>
    </div>

    <h3 style="font-size: 15px; margin-bottom: 12px; font-weight: 800; text-transform: uppercase;">📦 Purchased Items Roster</h3>
    <table class="item-breakdown-table">
        <thead>
            <tr>
                <th>Product Description</th>
                <th style="text-align: center; width: 70px;">Qty</th>
                <th style="text-align: right; width: 100px;">Unit Price</th>
                <th style="text-align: right; width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($items_query && mysqli_num_rows($items_query) > 0):
                while ($item = mysqli_fetch_assoc($items_query)):
                    $line_total = $item['price'] * $item['qty'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td style="text-align: center; font-weight: bold;"><?php echo $item['qty']; ?></td>
                    <td style="text-align: right;">₹<?php echo number_format($item['price'], 2); ?></td>
                    <td style="text-align: right; font-weight: bold;">₹<?php echo number_format($line_total, 2); ?></td>
                </tr>
            <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td>Consolidated Roster Items Package</td>
                    <td style="text-align: center; font-weight: bold;">1</td>
                    <td style="text-align: right;">₹<?php echo number_format($order_meta['total_amount'] - 45, 2); ?></td>
                    <td style="text-align: right; font-weight: bold;">₹<?php echo number_format($order_meta['total_amount'] - 45, 2); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px; color: #7c2d12;">
            <span>Logistics Dispatch Method:</span>
            <strong>Standard Courier Van Routing</strong>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 14px; color: #7c2d12;">
            <span>Fixed Outward Shipping Fee:</span>
            <strong>₹45.00</strong>
        </div>
    </div>
    
    <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 20px 0;">

    <div style="display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 20px; padding: 5px 0;">
        <span>TOTAL CHARGE DUE:</span>
        <span style="color: #10b981;">₹<?php echo number_format($order_meta['total_amount'], 2); ?></span>
    </div>
    
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 6px; text-align: center; color: #16a34a; font-size: 13px; font-weight: 700; margin-top: 15px;">
        💵 Payment Method: Cash on Delivery (COD) Collection Required Upon Arrival
    </div>
    
    <div style="margin-top: 40px; text-align: center;">
        <a href="shop.php" class="home-btn-link">
            🛒 Return to Shop Homepage
        </a>
    </div>

    <p style="text-align: center; font-size: 11px; color: #94a3b8; margin-top: 35px; margin-bottom: 0;">
        Thank you for purchasing your fresh groceries from FreshCart!<br>
        Press <kbd style="background:#f1f5f9; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-size:10px;">Ctrl</kbd> + <kbd style="background:#f1f5f9; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-size:10px;">P</kbd> to output paper copies.
    </p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    window.history.pushState(null, "", window.location.href);
    window.onpopstate = function() {
        window.location.href = "shop.php";
    };
});
</script>

</body>
</html>