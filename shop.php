<?php
session_start();

// Connect to the core database
$conn = mysqli_connect("localhost", "root", "", "grocery_db");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 1. Fetch all parent categories
$categories_result = mysqli_query($conn, "SELECT * FROM category");

// 2. Fetch all subcategories to build the dynamic sub-filter rows
$subcategories_result = mysqli_query($conn, "SELECT * FROM sub_category");
$subcategories = [];
while ($sub = mysqli_fetch_assoc($subcategories_result)) {
    $subcategories[] = $sub;
}

// 3. Fetch active products along with category and subcategory names
$products_result = mysqli_query(
    $conn,
    "SELECT p.*, c.category_name, s.sub_category_name 
     FROM product p 
     JOIN category c ON p.category_id = c.category_id 
     JOIN sub_category s ON p.sub_category_id = s.sub_category_id
     WHERE p.quantity > 0 
     ORDER BY p.id DESC"
);

// ====================================================================
// 📊 UNIFIED NAVBAR BADGE COUNT CHECKER ENGINE
// ====================================================================
$cart_count = 0;
if (isset($_SESSION['customer_id'])) {
    // If logged in, fetch the total unique lines in their database cart table
    $user_id = intval($_SESSION['customer_id']);
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as unique_items FROM cart WHERE user_id = $user_id");
    $count_row = mysqli_fetch_assoc($count_res);
    $cart_count = intval($count_row['unique_items']);
} else {
    // 🟢 FIXED GUEST TRACKING: Read unique item rows directly out of session_cart table
    $browser_token = isset($_COOKIE['guest_browser_token']) ? mysqli_real_escape_string($conn, $_COOKIE['guest_browser_token']) : '';
    if (!empty($browser_token)) {
        $count_res = mysqli_query($conn, "SELECT COUNT(*) as unique_items FROM session_cart WHERE browser_token = '$browser_token'");
        $count_row = mysqli_fetch_assoc($count_res);
        $cart_count = intval($count_row['unique_items']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshCart - Modern Online Grocery Store</title>
    <link rel="stylesheet" href="shop.css?v=1.3">
    <style>
        .shop-subcategory-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 35px;
            overflow-x: auto;
            padding-bottom: 8px;
            transition: all 0.3s ease;
        }
        .sub-filter-pill {
            background-color: #f1f5f9;
            color: #475569;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .sub-filter-pill:hover, .sub-filter-pill.active {
            background-color: #10b981;
            color: #ffffff;
            border-color: #10b981;
        }
        .qty-btn {
            padding: 8px 14px;
            color: #475569;
            background: none;
            border: none;
            font-weight: 800;
            font-size: 16px;
            user-select: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .qty-btn:hover {
            background-color: #e2e8f0;
        }
    </style>
</head>
<body class="shop-body">

   <nav class="shop-navbar">
        <a href="shop.php" class="brand-logo">Fresh<span>Cart</span></a>
        
        <div class="search-container">
            <input type="text" id="storeSearch" class="search-bar" placeholder="Search for fresh fruits, premium dairy, or pantry essentials...">
        </div>

        <div class="nav-right-group" style="display: flex; align-items: center; gap: 15px;">
    
    <a href="basket.php" class="cart-widget">
        <span>🛒 Basket</span>
        <span class="cart-badge" id="cartCount"><?php echo $cart_count; ?></span>
    </a>

    <div class="user-profile-dropdown">
        <?php if (isset($_SESSION['customer_id'])): ?>
            
            <button class="profile-trigger" onclick="toggleDropdown()">
                👤 <?php echo htmlspecialchars($_SESSION['customer_name']); ?> ▼
            </button>
            
            <div class="dropdown-menu" id="profileDropdown">
                <a href="my_orders.php">📦 My Orders</a>
                <a href="edit_profile.php">⚙️ Account Settings</a>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 4px 0;">
                <a href="logout_customer.php" style="color: #ef4444;">🚪 Sign Out</a>
            </div>

        <?php else: ?>
            <a href="login.php" class="shop-filter-pill active" style="text-decoration: none; padding: 8px 20px;">
                Sign In
            </a>
        <?php endif; ?>
    </div>

    </nav>

    <main class="shop-main">
        
        <div class="shop-hero-banner">
            <h1>Freshness Handpicked For You</h1>
            <p>Explore farm-fresh groceries uploaded by our management team, delivered straight to your door.</p>
        </div>

        <div class="shop-category-bar">
            <div class="shop-filter-pill active" onclick="filterParentCategory('all', this)">All Categories</div>
            <?php mysqli_data_seek($categories_result, 0); ?>
            <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                <div class="shop-filter-pill" data-catid="<?php echo $cat['category_id']; ?>" onclick="filterParentCategory('<?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>', this)">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="shop-subcategory-bar" id="subCategoryRow" style="display: none;">
            </div>

        <div class="shop-grid" id="productGrid">
            
            <?php if(mysqli_num_rows($products_result) > 0): ?>
                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                    
                    <div class="shop-item-card" 
                         data-name="<?php echo strtolower(htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8')); ?>" 
                         data-category="<?php echo htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8'); ?>"
                         data-subcategory="<?php echo htmlspecialchars($product['sub_category_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div>
                            <div class="shop-card-img-holder">
                                <img src="admin/images/<?php echo htmlspecialchars($product['image']); ?>" alt="Grocery Item Preview">
                            </div>
                            <div class="shop-card-tag"><?php echo htmlspecialchars($product['category_name']); ?> &rsaquo; <?php echo htmlspecialchars($product['sub_category_name']); ?></div>
                            <h3 class="shop-card-title" title="<?php echo htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </h3>
                            <div class="shop-card-meta">Metric Unit: <strong><?php echo htmlspecialchars($product['unit']); ?></strong></div>
                        </div>

                        <div>
                            <div class="shop-card-pricing">
                                <span class="current-amt">₹<?php echo $product['selling_price']; ?></span>
                                <?php if($product['master_price'] > $product['selling_price']): ?>
                                    <span class="strike-amt">₹<?php echo $product['master_price']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="purchase-action" style="margin-top: 15px; min-height: 38px;">
                                <?php 
                                $p_id = $product['id'];
                                
                                // Fetch initial quantity based on authentication states
                                $current_qty = 0;
                                if (isset($_SESSION['customer_id'])) {
                                    $u_id = intval($_SESSION['customer_id']);
                                    $qty_check = mysqli_query($conn, "SELECT qty FROM cart WHERE user_id = $u_id AND product_id = $p_id");
                                    if ($qty_row = mysqli_fetch_assoc($qty_check)) {
                                        $current_qty = intval($qty_row['qty']);
                                    }
                                } else {
                                    // 🟢 FIXED GUEST CARD STATE: Look up quantities inside session_cart table
                                    $browser_token = isset($_COOKIE['guest_browser_token']) ? mysqli_real_escape_string($conn, $_COOKIE['guest_browser_token']) : '';
                                    if (!empty($browser_token)) {
                                        $qty_check = mysqli_query($conn, "SELECT qty FROM session_cart WHERE browser_token = '$browser_token' AND product_id = $p_id");
                                        if ($qty_row = mysqli_fetch_assoc($qty_check)) {
                                            $current_qty = intval($qty_row['qty']);
                                        }
                                    }
                                }
                                ?>
                                
                                <div class="qty-updater-wrapper" id="updater-<?php echo $p_id; ?>" style="display: <?php echo ($current_qty > 0) ? 'flex' : 'none'; ?>; align-items: center; justify-content: space-between; background: #f8fafc; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden; max-width: 140px; margin: 0 auto;">
                                    <button type="button" class="qty-btn decrease-btn" data-id="<?php echo $p_id; ?>" style="color: #ef4444;">−</button>
                                    <span class="qty-count-text" style="font-weight: 700; color: #0f172a; font-size: 15px;"><?php echo $current_qty; ?></span>
                                    
                                    <?php if ($current_qty < $product['quantity']): ?>
                                        <button type="button" class="qty-btn increase-btn" data-id="<?php echo $p_id; ?>" data-max="<?php echo $product['quantity']; ?>" style="color: #10b981;">+</button>
                                    <?php else: ?>
                                        <span class="max-reached-indicator" style="padding: 8px 14px; color: #cbd5e1; font-weight: 800; font-size: 16px; cursor: not-allowed; user-select: none;">+</span>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="shop-buy-btn initial-add-btn" id="add-btn-<?php echo $p_id; ?>" data-id="<?php echo $p_id; ?>" style="display: <?php echo ($current_qty > 0) ? 'none' : 'block'; ?>; width: 100%; text-align: center; border: none; cursor: pointer;">
                                    + Add to Basket
                                </button>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?> 
            <?php else: ?>
                <div class="shop-empty-notice" style="grid-column: 1/-1;">
                    <h3 style="margin-bottom: 10px; color: #2c3e50;">No Stock Available 📦</h3>
                    <p>Check back later! Our staff is currently restocking fresh grocery products.</p>
                </div>
            <?php endif; ?>

            <div class="shop-empty-notice" id="noResultsBlock" style="display: none; grid-column: 1/-1;">No matching products found...</div>
        </div>

    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Listen globally for catalog selection click event coordinates
        document.body.addEventListener('click', function(e) {
            
            if (e.target.classList.contains('initial-add-btn')) {
                const productId = e.target.getAttribute('data-id');
                executeAsynchronousCartUpdate(productId, 'add');
            }
            
            if (e.target.classList.contains('increase-btn')) {
                const productId = e.target.getAttribute('data-id');
                executeAsynchronousCartUpdate(productId, 'add');
            }
            
            if (e.target.classList.contains('decrease-btn')) {
                const productId = e.target.getAttribute('data-id');
                executeAsynchronousCartUpdate(productId, 'remove');
            }
        });

        function executeAsynchronousCartUpdate(productId, actionType) {
            const payload = new FormData();
            payload.append('product_id', productId);
            payload.append('action', actionType);

            // Dispatches updates background channels safely without interrupting viewport scroll position
            fetch('update_cart_ajax.php', {
                method: 'POST',
                body: payload
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const wrapper = document.getElementById('updater-' + productId);
                    const addBtn = document.getElementById('add-btn-' + productId);
                    const qtyLabel = wrapper.querySelector('.qty-count-text');
                    const navBadge = document.getElementById('cartCount');

                    // 1. Sync top navigation cart balance badge numbers
                    if (navBadge) {
                        navBadge.textContent = data.unique_items_count;
                    }

                    // 2. Adjust interactive visibility toggles seamlessly inside the DOM
                    if (data.new_quantity > 0) {
                        qtyLabel.textContent = data.new_quantity;
                        wrapper.style.display = 'flex';
                        addBtn.style.display = 'none';
                    } else {
                        wrapper.style.display = 'none';
                        addBtn.style.display = 'block';
                    }
                }
            })
            .catch(err => console.error("AJAX Processing Interruption Error:", err));
        }
    });
    </script>

    <script>
        const subcategoriesCache = <?php echo json_encode($subcategories); ?>;
        const searchInput = document.getElementById('storeSearch');
        const productCards = document.querySelectorAll('.shop-item-card');
        const noResultsBlock = document.getElementById('noResultsBlock');
        const subCategoryRow = document.getElementById('subCategoryRow');

        let activeParentCategory = 'all';
        let activeSubCategory = 'all';

        function filterParentCategory(categoryName, element) {
            document.querySelectorAll('.shop-category-bar .shop-filter-pill').forEach(pill => pill.classList.remove('active'));
            element.classList.add('active');
            activeParentCategory = categoryName;
            activeSubCategory = 'all'; 
            
            if (categoryName === 'all') {
                subCategoryRow.style.display = "none";
                subCategoryRow.innerHTML = "";
            } else {
                const categoryId = element.getAttribute('data-catid');
                renderSubCategoryPills(categoryId);
            }
            runCombinedFilter();
        }

        function renderSubCategoryPills(categoryId) {
            const filteredSubs = subcategoriesCache.filter(sub => sub.category_id == categoryId);
            if (filteredSubs.length === 0) {
                subCategoryRow.style.display = "none";
                return;
            }
            let html = `<div class="sub-filter-pill active" onclick="filterSubCategory('all', this)">All under ${activeParentCategory}</div>`;
            filteredSubs.forEach(sub => {
                html += `<div class="sub-filter-pill" onclick="filterSubCategory('${sub.sub_category_name}', this)">${sub.sub_category_name}</div>`;
            });
            subCategoryRow.innerHTML = html;
            subCategoryRow.style.display = "flex";
        }

        function filterSubCategory(subCategoryName, element) {
            document.querySelectorAll('.sub-filter-pill').forEach(pill => pill.classList.remove('active'));
            element.classList.add('active');
            activeSubCategory = subCategoryName;
            runCombinedFilter();
        }

        searchInput.addEventListener('input', runCombinedFilter);

        function runCombinedFilter() {
            const searchVal = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            productCards.forEach(card => {
                const cardName = card.getAttribute('data-name');
                const cardCat = card.getAttribute('data-category');
                const cardSub = card.getAttribute('data-subcategory');

                const matchesParent = (activeParentCategory === 'all' || cardCat === activeParentCategory);
                const matchesSub = (activeSubCategory === 'all' || cardSub === activeSubCategory);
                const matchesSearch = cardName.includes(searchVal);

                if (matchesParent && matchesSub && matchesSearch) {
                    card.style.display = "flex";
                    visibleCount++;
                } else {
                    card.style.display = "none";
                }
            });

            if (visibleCount === 0 && productCards.length > 0) {
                noResultsBlock.style.display = "block";
            } else {
                noResultsBlock.style.display = "none";
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }
        window.onclick = function(event) {
            if (!event.target.matches('.profile-trigger')) {
                const dropdowns = document.getElementsByClassName("dropdown-menu");
                for (let i = 0; i < dropdowns.length; i++) {
                    let openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>