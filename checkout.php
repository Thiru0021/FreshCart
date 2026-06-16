<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['customer_id']);

// 1. Fetch current customer details out of profile records
$customer_query = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id = $user_id");
$customer_meta = mysqli_fetch_assoc($customer_query);

// 2. Fetch current database cart records
$cart_query = mysqli_query($conn, "SELECT c.*, p.product_name, p.selling_price, p.image, p.unit 
                                   FROM cart c 
                                   JOIN product p ON c.product_id = p.id 
                                   WHERE c.user_id = $user_id");

if (mysqli_num_rows($cart_query) == 0) {
    header("Location: shop.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Clearance - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    <style>
        .checkout-layout { display: flex; gap: 40px; align-items: flex-start; margin-top: 30px; }
        .address-pane { flex: 1.3; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; }
        .summary-pane { flex: 1; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; position: sticky; top: 20px; }
        .form-field-block { margin-bottom: 20px; }
        .form-field-block label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #334155; }
        .form-input-ctrl { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .readonly-input { background-color: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .warning-notice { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 600; font-size: 14px; }
        
        /* 🛠️ PAYMENT OPTION INTERACTIVE STYLING MATRIX */
        .payment-method-card { display: flex; align-items: center; background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s ease; }
        .payment-method-card:hover { border-color: #cbd5e1; background: #f1f5f9; }
        .payment-method-card input[type="radio"] { margin-right: 12px; width: 18px; height: 18px; accent-color: #10b981; cursor: pointer; }
        .payment-method-card label { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1e293b; cursor: pointer; width: 100%; user-select: none; }
        .online-payment-alert { background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-top: -6px; margin-bottom: 15px; display: none; }
    </style>
</head>
<body class="shop-body">

<div class="shop-main" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
    <h1>Settlement Clearance & Logistics</h1>
    
    <?php if (empty($customer_meta['address']) || empty($customer_meta['phone'])): ?>
        <div class="warning-notice">
            ⚠️ Your profile delivery parameters are incomplete! Please configure a primary mobile number and home address inside your account settings panel before placing orders.<br><br>
            <a href="accounts.php" style="color:#c2410c; text-decoration:underline;">Click here to set up your account address profile &rarr;</a>
        </div>
    <?php endif; ?>

    <form action="place_order.php" method="POST">
        <div class="checkout-layout">
            
            <div class="address-pane">
                <h3 style="margin-top:0; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">1. Billing Coordinates</h3>
                <div class="form-field-block">
                    <label>Account Bound Email</label>
                    <input type="email" class="form-input-ctrl readonly-input" value="<?php echo htmlspecialchars($customer_meta['email']); ?>" readonly>
                </div>
                <div class="form-field-block">
                    <label>Profile Registered Address (Read-Only)</label>
                    <textarea id="billing_address" class="form-input-ctrl readonly-input" readonly style="height:80px; resize:none;"><?php echo htmlspecialchars($customer_meta['address'] ?? 'No saved address profile detected.'); ?></textarea>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; margin-bottom: 25px;">
                    <input type="checkbox" id="sync_address_check" onclick="toggleShippingSync()" style="cursor: pointer;">
                    <label for="sync_address_check" style="font-weight:700; color:#16a34a; cursor:pointer; margin-left:6px; user-select:none;">Shipping destination parameters match billing records</label>
                </div>

                <h3 style="border-bottom:1px solid #f1f5f9; padding-bottom:10px;">2. Shipping Logistics Drop Location</h3>
                <div class="form-field-block">
                    <label>Consignee Mobile Contact Number</label>
                    <input type="tel" name="shipping_phone" id="shipping_phone" class="form-input-ctrl" value="<?php echo htmlspecialchars($customer_meta['phone'] ?? ''); ?>" required>
                </div>
                <div class="form-field-block">
                    <label>Consignee Destination Email</label>
                    <input type="email" name="shipping_email" id="shipping_email" class="form-input-ctrl" value="<?php echo htmlspecialchars($customer_meta['email']); ?>" required>
                </div>
                <div class="form-field-block">
                    <label>Delivery Routing Address Details</label>
                    <textarea id="shipping_address" name="shipping_address" class="form-input-ctrl" required style="height:90px;"></textarea>
                </div>
            </div>

            <div class="summary-pane">
                <h3 style="margin-top:0;">3. Order Invoice Items</h3>
                <?php 
                $subtotal = 0;
                $delivery = 45.00;
                while($item = mysqli_fetch_assoc($cart_query)): 
                    $line_total = $item['selling_price'] * $item['qty'];
                    $subtotal += $line_total;
                ?>
                    <div style="display: flex; justify-content:space-between; font-size:14px; margin-bottom:12px; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['qty']; ?>)</span>
                        <strong>₹<?php echo number_format($line_total, 2); ?></strong>
                    </div>
                <?php endwhile; ?>

                <div style="display:flex; justify-content:space-between; margin:8px 0; font-size:14px; color:#475569;">
                    <span>Items Subtotal</span>
                    <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin:8px 0; font-size:14px; color:#475569;">
                    <span>Shipping Handling</span>
                    <strong>₹45.00</strong>
                </div>
                
                <hr style="border:0; border-top:1px dashed #cbd5e1; margin:15px 0;">
                <?php $grand_total = $subtotal + $delivery; ?>
                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:18px; margin-bottom:25px;">
                    <span>Grand Total Bill</span>
                    <span style="color:#064e3b;">₹<?php echo number_format($grand_total, 2); ?></span>
                </div>

                <h3>4. Settlement Authorization</h3>
                
                <div class="payment-method-card">
                    <input type="radio" name="payment_option" value="COD" id="payment_cod" checked onclick="evaluatePaymentUI()">
                    <label for="payment_cod">💵 Cash on Delivery (COD)</label>
                </div>
                
                <div class="payment-method-card">
                    <input type="radio" name="payment_option" value="ONLINE" id="payment_online" onclick="evaluatePaymentUI()">
                    <label for="payment_online">💳 UPI / Credit Card / NetBanking</label>
                </div>
                
                <div class="online-payment-alert" id="online_notice_panel">
                    ⚡ You will be forwarded directly to the secure payment portal gateway post transaction commitment.
                </div>
                
                <?php if (empty($customer_meta['address']) || empty($customer_meta['phone'])): ?>
                    <button type="button" class="shop-buy-btn" style="width:100%; padding:14px; background:#cbd5e1; color:#94a3b8; border:none; border-radius:8px; font-size:16px; font-weight:700; cursor:not-allowed;" disabled>Configure Profile First</button>
                <?php else: ?>
                    <button type="submit" style="width:100%; padding:14px; background:#064e3b; color:white; border:none; border-radius:8px; font-size:16px; font-weight:700; cursor:pointer;">Commit Order Transaction →</button>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<script>
function toggleShippingSync() {
    const checker = document.getElementById('sync_address_check');
    const billingText = document.getElementById('billing_address').value;
    const shippingTarget = document.getElementById('shipping_address');
    const emailSource = "<?php echo $_SESSION['customer_email']; ?>";
    const shippingEmailTarget = document.getElementById('shipping_email');
    const profilePhone = "<?php echo htmlspecialchars($customer_meta['phone'] ?? ''); ?>";
    const shippingPhoneTarget = document.getElementById('shipping_phone');

    if (checker.checked) {
        shippingTarget.value = billingText;
        shippingEmailTarget.value = emailSource;
        shippingPhoneTarget.value = profilePhone;
    } else {
        shippingTarget.value = "";
        shippingEmailTarget.value = "";
        shippingPhoneTarget.value = "";
    }
}

// 🧠 DYNAMIC PAYMENT VISIBILITY INTERCEPTOR
function evaluatePaymentUI() {
    const onlineRadio = document.getElementById('payment_online');
    const noticePanel = document.getElementById('online_notice_panel');
    
    if (onlineRadio.checked) {
        noticePanel.style.display = 'block';
    } else {
        noticePanel.style.display = 'none';
    }
}
</script>
</body>
</html>