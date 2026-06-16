# 🛒 FreshCart - Modern Online Grocery Store

FreshCart is a high-fidelity, full-stack e-commerce web application built for seamless grocery shopping. It features dual-state shopping cart mechanics (retaining guest selections securely via persistent database tokens), an asynchronous AJAX-driven interface, a dynamic catalog filtering system, and an index-optimized database structure.

---

## ✨ Features

### 🛍️ Customer Storefront
* **Unified State Management (CRUD):** Smooth, background cart updates using asynchronous JavaScript Fetch API.
* **Persistent Guest Carts:** Guest items are retained across browser restarts via long-lived `guest_browser_token` cookie handshakes mapped directly to a dedicated `session_cart` table.
* **Seamless Login Merge & Purge:** When a guest logs in, their temporary database selections automatically combine with their profile's permanent cart, checking warehouse ceilings before running an automated data purge.
* **Dynamic Search & Sub-category Filter:** Live client-side product filtering by text queries, primary parent categories, and responsive sub-category pills.

### 🛡️ Logistics Admin Control Panel
* **Customer Management:** Dynamic dashboard to review registered user database listings.
* **Interactive Order Queue:** Real-time logistics portal where operators can view total customer bills, payment methods, and instantly alter fulfillment statuses (`Pending` ⏳, `Shipped` 🚚, `Delivered` ✅).
* **Polished Professional Identifier Masks:** Automatic string sequence outputs formatting raw database integers into high-end presentation labels (`cus001`, `ord014`) on-screen.

---

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3 (Modern Glassmorphic UI with variables & radial gradients), JavaScript (ES6+ Fetch API, Custom Event Delegation)
* **Backend:** PHP 8.x (Session management, secure password verification mapping)
* **Database:** MySQL / MariaDB (Relational design, optimized primary indexing)

---

## 💾 Database Schema Blueprints

The core engine relies on the following relational structure inside the `grocery_db` database:

* **`customers`** - Registered user profiles.
* **`product`** - Managed item descriptions, tracking unit sizes, images, pricing structures, and warehouse quantities.
* **`category`** & **`sub_category`** - Relational item organization nodes.
* **`cart`** - Permanent table holding items selected by authenticated users.
* **`session_cart`** - Volatile database table mapping browser tokens (`ss001` sequential tracking) to unauthenticated visitors.
* **`orders`** - Logistics records storing totals, destination vectors, payment states, and shipping checkpoints.

---
## 📸 Project Preview

### Customer Storefront
<img src="screenshots/store_front.png" width="800" alt="FreshCart Storefront Menu">

### Dynamic Shopping Basket
<img src="screenshots/basket.png" width="800" alt="Shopping Basket View">

### Admin Logistics Dashboard
<img src="screenshots/admin_dashboard.png" width="800" alt="Admin Order Queue Control Panel">
---

## 🚀 Local Installation & Setup

1. **Clone the Repository:**
```bash
   git clone [https://github.com/YOUR_USERNAME/grocery-shop.git](https://github.com/YOUR_USERNAME/grocery-shop.git)
