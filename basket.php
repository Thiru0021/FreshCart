<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 1. DATA READ PHASE: Read values cleanly based on client authentication states
$subtotal = 0;
$delivery_charge = 45; 
$cart_items = [];

if (isset($_SESSION['customer_id'])) {
    // ====================================================================
    // 👤 PATH A: AUTHENTICATED CUSTOMER READ
    // ====================================================================
    $user_id = intval($_SESSION['customer_id']);
    $query = "SELECT c.qty AS basket_qty, p.*, cat.category_name 
              FROM cart c 
              JOIN product p ON c.product_id = p.id 
              JOIN category cat ON p.category_id = cat.category_id
              WHERE c.user_id = $user_id";
    $db_result = mysqli_query($conn, $query);
    if ($db_result) {
        while ($row = mysqli_fetch_assoc($db_result)) {
            $cart_items[] = $row;
        }
    }
} else {
    // ====================================================================
    // 🚪 PATH B: ANONYMOUS GUEST READ (Fixed to match session_cart table)
    // ====================================================================
    // Grab the persistent browser identity token cookie assigned by update_cart_ajax.php
    $browser_token = isset($_COOKIE['guest_browser_token']) ? mysqli_real_escape_string($conn, $_COOKIE['guest_browser_token']) : '';
    
    if (!empty($browser_token)) {
        // Query your newly built session_cart table instead of checking server memory sessions
        $query = "SELECT sc.qty AS basket_qty, p.*, cat.category_name 
                  FROM session_cart sc
                  JOIN product p ON sc.product_id = p.id 
                  JOIN category cat ON p.category_id = cat.category_id 
                  WHERE sc.browser_token = '$browser_token'";
                  
        $db_result = mysqli_query($conn, $query);
        if ($db_result) {
            while ($row = mysqli_fetch_assoc($db_result)) {
                // Maintained alias alias 'basket_qty' so your HTML code renders flawlessly
                $cart_items[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Basket - FreshCart</title>
    <link rel="stylesheet" href="shop.css">
    <style>
        .basket-wrapper { display: flex; gap: 30px; align-items: flex-start; margin-top: 20px; }
        .basket-table-card { flex: 2; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; }
        .order-summary-card { flex: 1; background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; position: sticky; top: 20px; }
        .qty-control-btn { padding: 6px 12px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; color: #1e293b; }
        .basket-qty-display { display: inline-block; width: 30px; text-align: center; font-weight: 700; }
    </style>
</head>
<body class="shop-body">

    <nav class="shop-navbar">
        <a href="shop.php" class="brand-logo">Fresh<span>Cart</span></a>
        <a href="shop.php" class="cart-widget" style="background-color: #f1f5f9; text-decoration: none; font-weight: 600;">
            &larr; Continue Shopping
        </a>
    </nav>

    <main class="shop-main">
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px; color: #0f172a;">Review Your Basket</h1>
        <p style="color: #64748b; margin-bottom: 30px;">Verify parameters, change units weight requirements, or proceed to settlement clearance.</p>

        <?php if (empty($cart_items)): ?>
            <div class="shop-empty-notice" style="padding: 80px 20px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🛒</div>
                <h3 style="margin-bottom: 10px; color: #0f172a;">Your basket is currently empty!</h3>
                <p style="color: #64748b; margin-bottom: 25px;">Looks like you haven't added any fresh groceries to your tray yet.</p>
                <a href="shop.php" class="shop-buy-btn" style="display: inline-flex; width: auto; padding: 12px 30px; text-decoration: none; border-radius: 8px;">Browse Store Catalog</a>
            </div>
        <?php else: ?>
            
            <div class="basket-wrapper">
                
                <div class="basket-table-card">
                    <table class="basket-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #cbd5e1; text-align: left;">
                                <th style="padding-bottom: 12px;">Product Details</th>
                                <th style="text-align: center; padding-bottom: 12px;">Quantity</th>
                                <th style="text-align: right; padding-bottom: 12px;">Total Price</th>
                                <th style="text-align: right; padding-bottom: 12px; width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($cart_items as $item): 
                                $qty = $item['basket_qty'];
                                $item_total = $item['selling_price'] * $qty;
                                $subtotal += $item_total;
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;" data-row-id="<?php echo $item['id']; ?>">
                                <td style="padding: 16px 0;">
                                    <div class="basket-item-meta" style="display: flex; gap: 15px; align-items: center;">
                                        <img src="admin/images/<?php echo htmlspecialchars($item['image']); ?>" alt="Grocery Preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <div>
                                            <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #10b981; display: block; margin-bottom: 2px;"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                            <strong style="font-size: 16px; color: #0f172a; display: block;"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                            <span style="font-size: 13px; color: #64748b;">Unit Size: <?php echo htmlspecialchars($item['unit']); ?> @ ₹<span class="unit-price"><?php echo $item['selling_price']; ?></span></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; white-space: nowrap; padding: 16px 0;">
                                    <button type="button" class="qty-control-btn basket-ajax-btn decrease-basket" data-id="<?php echo $item['id']; ?>">−</button>
                                    <span class="basket-qty-display row-qty-text"><?php echo $qty; ?></span>
                                    <button type="button" class="qty-control-btn basket-ajax-btn increase-basket" data-id="<?php echo $item['id']; ?>">+</button>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 16px; padding: 16px 0;">
                                    ₹<span class="row-total-price"><?php echo number_format($item_total, 2); ?></span>
                                </td>
                                <td style="text-align: right; padding: 16px 0;">
                                    <a href="javascript:void(0);" class="remove-item-link inline-purge-trigger" data-id="<?php echo $item['id']; ?>" style="color: #ef4444; font-size: 13px; font-weight: 600; text-decoration: none;">Remove</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php $grand_total = $subtotal + $delivery_charge; ?>
                <div class="order-summary-card">
                    <h3 style="margin-top: 0; margin-bottom: 20px; color: #0f172a;">Order Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin: 12px 0; color: #475569; font-size: 14px;">
                        <span>Items Subtotal</span>
                        <span style="font-weight: 600; color: #0f172a;">₹<span id="summarySubtotal"><?php echo number_format($subtotal, 2); ?></span></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 12px 0; color: #475569; font-size: 14px;">
                        <span>Estimated Shipping / Handling</span>
                        <span style="font-weight: 600; color: #0f172a;">₹<span id="summaryShipping" data-fee="<?php echo $delivery_charge; ?>"><?php echo number_format($delivery_charge, 2); ?></span></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 12px 0; color: #475569; font-size: 14px;">
                        <span>Tax Charges (GST Included)</span>
                        <span style="font-weight: 600; color: #0f172a;">₹0.00</span>
                    </div>
                    
                    <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 15px 0;">
                    
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 18px; margin-bottom: 25px; color: #0f172a;">
                        <span>Total Checkout Bill</span>
                        <span style="color: #064e3b;">₹<span id="summaryGrandTotal"><?php echo number_format($grand_total, 2); ?></span></span>
                    </div>

                    <form action="checkout_process.php" method="POST">
                        <input type="hidden" name="grand_total" id="hiddenGrandTotal" value="<?php echo $grand_total; ?>">
                        <button type="submit" class="shop-buy-btn" style="width: 100%; padding: 14px; background-color: #064e3b; text-align: center; font-size: 16px; border: none; color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            Proceed to Checkout →
                        </button>
                    </form>
                </div>

            </div>
        <?php endif; ?>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Intercept inline row counter events asynchronously
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('basket-ajax-btn')) {
                const productId = e.target.getAttribute('data-id');
                const action = e.target.classList.contains('increase-basket') ? 'add' : 'remove';
                executeLiveBasketMutation(productId, action);
            }

            if (e.target.classList.contains('inline-purge-trigger')) {
                if (confirm('Remove item entirely from basket?')) {
                    const productId = e.target.getAttribute('data-id');
                    executeLiveBasketMutation(productId, 'remove_all');
                }
            }
        });

        function executeLiveBasketMutation(productId, actionType) {
            const payload = new FormData();
            payload.append('product_id', productId);
            payload.append('action', actionType);

            fetch('update_cart_ajax.php', {
                method: 'POST',
                body: payload
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.querySelector(`tr[data-row-id="${productId}"]`);
                    
                    if (data.new_quantity <= 0 || actionType === 'remove_all') {
                        if (row) row.remove();
                        if (data.unique_items_count <= 0) {
                            window.location.reload();
                            return;
                        }
                    } else {
                        const qtyText = row.querySelector('.row-qty-text');
                        const unitPrice = parseFloat(row.querySelector('.unit-price').textContent);
                        const rowTotalDisplay = row.querySelector('.row-total-price');

                        qtyText.textContent = data.new_quantity;
                        rowTotalDisplay.textContent = (unitPrice * data.new_quantity).toFixed(2);
                    }

                    recalculateGlobalRosterTotals();
                }
            })
            .catch(err => console.error("Pipeline breakdown:", err));
        }

        function recalculateGlobalRosterTotals() {
            let freshSubtotal = 0;
            const remainingRows = document.querySelectorAll('tr[data-row-id]');

            remainingRows.forEach(row => {
                const unitPrice = parseFloat(row.querySelector('.unit-price').textContent);
                const currentQty = parseInt(row.querySelector('.row-qty-text').textContent);
                freshSubtotal += (unitPrice * currentQty);
            });

            const shippingFee = parseFloat(document.getElementById('summaryShipping').getAttribute('data-fee'));
            const finalBill = freshSubtotal + shippingFee;

            document.getElementById('summarySubtotal').textContent = freshSubtotal.toFixed(2);
            document.getElementById('summaryGrandTotal').textContent = finalBill.toFixed(2);
            document.getElementById('hiddenGrandTotal').value = finalBill.toFixed(2);
        }
    });
    </script>
</body>
</html>