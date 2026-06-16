<?php
session_start();

// 1. Establish database connection link
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

// 2. Authentication Protection: Ensure a customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);

// 3. READ: Fetch only this specific customer's transactions ordered by newest first
$query = "SELECT order_id, total_amount, delivery_address, order_status, created_at 
          FROM orders 
          WHERE customer_id = $customer_id 
          ORDER BY order_id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - FreshCart</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 40px 20px; }
        .container { max-width: 850px; margin: 0 auto; }
        .header { margin-bottom: 30px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 28px; }
        .header p { margin: 5px 0 0; color: #64748b; }
        .order-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 15px; }
        .order-id { font-size: 16px; font-weight: 700; color: #0f172a; }
        .order-date { font-size: 14px; color: #64748b; }
        .order-body { display: flex; justify-content: space-between; align-items: flex-start; }
        .details-col h4 { margin: 0 0 5px; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .details-col p { margin: 0; color: #0f172a; font-size: 15px; }
        .status-badge { padding: 8px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        
        /* Dynamic Status Badge Colors */
        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-shipped { background-color: #e0f2fe; color: #0284c7; }
        .status-delivered { background-color: #dcfce7; color: #15803d; }
        
        .no-orders { text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px solid #e2e8f0; color: #94a3b8; }
        .back-btn { display: inline-block; margin-bottom: 20px; color: #064e3b; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <a href="shop.php" class="back-btn">← Continue Shopping</a>
    
    <div class="header">
        <h1>Your Purchase History</h1>
        <p>Track your real-time grocery delivery updates and order parameters here.</p>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">Order #<?php echo $row['order_id']; ?></span>
                    <span class="order-date"><?php echo date('d M Y - h:i A', strtotime($row['created_at'])); ?></span>
                </div>
                
                <div class="order-body">
                    <div style="display: flex; gap: 40px;">
                        <div class="details-col">
                            <h4>Total Bill</h4>
                            <p style="font-weight: 700; color: #10b981;">₹<?php echo number_format($row['total_amount'], 2); ?></p>
                        </div>
                        <div class="details-col">
                            <h4>Delivery Destination</h4>
                            <p style="max-width: 350px; font-size: 14px; color: #475569;"><?php echo htmlspecialchars($row['delivery_address']); ?></p>
                        </div>
                    </div>

                    <div>
                        <?php 
                        $status = $row['order_status'];
                        $badge_class = "status-pending";
                        $display_text = "⏳ Pending";

                        if ($status == 'Shipped') {
                            $badge_class = "status-shipped";
                            $display_text = "🚚 Shipped";
                        } elseif ($status == 'Delivered') {
                            $badge_class = "status-delivered";
                            $display_text = "✅ Delivered";
                        }
                        ?>
                        <span class="status-badge <?php echo $badge_class; ?>">
                            <?php echo $display_text; ?>
                        </span>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-orders">
            <h3>No orders placed yet</h3>
            <p>Your transaction logs are empty. Head back to our inventory catalogs to check out items!</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>