# 🛒 FreshMart - Online Grocery Store with Cart & Live Order Tracking

A full-stack, production-ready **Online Grocery Store** web application built with modern **PHP**, **MySQL (PDO)**, **Tailwind CSS**, and **JavaScript**.

Includes an interactive shopping experience, dynamic AJAX cart, multi-step checkout, and a **Live Visual Order Tracking System** with delivery milestones, estimated arrival times, and an **Admin Operations Dispatch Center**.

---

## 🌟 Key Features

### 1. 🥦 Grocery Browsing & Catalog
- **7 Fresh Aisles**: Fresh Fruits, Organic Vegetables, Dairy & Eggs, Bakery & Breads, Beverages & Juices, Pantry Staples, Snacks & Nuts.
- **Search & Filter**: Real-time keyword search, aisle category filtering, organic-certified toggle, and pricing sorts.
- **Produce Badges**: "100% Organic", discount badges (e.g. 20% OFF), packaging units (e.g., *1 kg*, *500 g*, *1 Gallon*, *Pack of 4*).
- **Product Details**: High-resolution imagery, nutrition/storage descriptions, quantity selectors, and aisle recommendations.

### 2. 🛍️ Shopping Cart & Checkout
- **Interactive AJAX Cart**: Add items without page reload, real-time bounce header count badge, and floating toast notifications.
- **Free Delivery Progress Bar**: Live indicator showing how much to add (e.g., "$12.50 away from FREE Express Delivery").
- **Quantity Controls**: Increment, decrement, or remove directly from cart.
- **Comprehensive Checkout**: Delivery coordinates, apartment/suite, mobile phone for driver calls, custom delivery notes, and payment options (**Cash on Delivery (COD)**, **Credit/Debit Card**, **Instant UPI/QR**).

### 3. 🚚 Real-Time Order Tracking System
- **Visual 5-Stage Delivery Pipeline**:
  1. `Order Placed` (Received & logged)
  2. `Processing` (Freshness check & sorting)
  3. `Packed` (Insulated thermal cooler bags with ice packs)
  4. `Out for Delivery` (Driver dispatched with vehicle/route updates)
  5. `Delivered` (Safely handed over at doorstep)
- **Live Activity Log**: Timestamped chronological milestone feed with location badges.
- **Instant Lookup**: Look up any active order using its Reference Number (`FM-YYYYMMDD-XXXX`) without having to create an account.
- **Customer Order History**: Logged-in customers can track all past and current orders directly from their profile.

### 4. ⚡ Admin Operations & Dispatch Center
- **Fulfillment Dashboard**: Gross revenue calculation, active in-transit delivery counter, order count, and low inventory reorder warnings.
- **Live Dispatch Control**: Select any customer order, change its delivery status, append custom driver notes, and set checkpoint locations. All changes **instantly update the customer's visual tracking page**!
- **Inventory Management**: Add new grocery items, edit pricing/units, and perform quick inline stock updates.

---

## 🚀 Quick Setup Instructions

### Option A: Using XAMPP / WAMP / MAMP (Recommended)

1. **Place the Project Folder**:
   Copy or keep the folder in your web server root:
   - XAMPP: `C:\xampp\htdocs\Grocery_store`
   - WAMP: `C:\wamp64\www\Grocery_store`

2. **Start Services**:
   Open XAMPP / WAMP Control Panel and start **Apache** and **MySQL**.

3. **Run 1-Click Database Setup Wizard**:
   Open your browser and navigate to:
   ```text
   http://localhost/Grocery_store/database/install.php
   ```
   Click the green **"Run 1-Click Database Installation"** button.
   The wizard will automatically:
   - Create the `grocery_store_db` database
   - Set up all schema tables
   - Seed **25+ fresh grocery items** across 7 categories
   - Create default admin and customer demo accounts
   - Seed an active **Demo Tracking Order** (`FM-20261004-9842`)

   *(Alternatively, you can manually import `database/schema.sql` via phpMyAdmin).*

4. **Visit the Store**:
   Open:
   ```text
   http://localhost/Grocery_store/
   ```

---

### Option B: Using PHP Built-In Web Server

If you already have PHP and MySQL running locally:
```bash
cd c:\Users\lenovo\Desktop\Grocery_store
php -S localhost:8000
```
Then visit `http://localhost:8000/database/install.php` to run setup!

---

## 🔑 Demo Login Credentials

| Role | Email | Password | Access / Features |
| :--- | :--- | :--- | :--- |
| **Store Admin** | `admin@freshmart.com` | `admin123` | Full Admin Dashboard, Dispatch & Tracking Management, Inventory Controls |
| **Customer Demo** | `sarah@freshmart.com` | `password123` | Shopping, Saved Delivery Address, Order History |

*Note: The login page includes 1-click credential buttons to automatically fill these in.*

---

## 📦 How to Test Live Order Tracking

1. **Test Existing Demo Order**:
   - Go to `http://localhost/Grocery_store/track-order.php`
   - Enter Reference Number: `FM-20261004-9842`
   - You will see a live 4th-stage delivery pipeline (**Out for Delivery**) with driver David en route!

2. **Test Placing a New Order**:
   - Add items to your cart from `shop.php`.
   - Go to `cart.php` &rarr; `checkout.php`.
   - Fill in your delivery address and click **"Place Order"**.
   - You'll be taken to `order-success.php` with your new Order Reference (e.g., `FM-20261004-XXXX`).
   - Click **"Track Order in Real-Time"**.

3. **Push Live Status Updates as Admin**:
   - Log in as Admin (`admin@freshmart.com` / `admin123`).
   - Go to **Orders & Tracking** (`admin/orders.php`).
   - Click **"Update Status & Log"** on your order.
   - Change the status (e.g. from `Placed` to `Packed` or `Out for Delivery`), enter a note (e.g. *"Driver Dave dispatched with refrigerated crate"*), and click **Push Status & Timeline Update**.
   - Refresh the customer's tracking screen to see the timeline and progress bar instantly update!

---

## 📂 Project Directory Structure

```text
Grocery_store/
├── config/
│   ├── config.php          # Store constants (tax rate, free shipping threshold, status pipeline)
│   └── db.php              # PDO database connection & helper utilities
├── database/
│   ├── schema.sql          # Complete MySQL schema & 25+ grocery seed items
│   └── install.php         # 1-click web setup wizard
├── includes/
│   ├── header.php          # Responsive top bar, search, aisles dropdown, cart count
│   └── footer.php          # Value proposition bar, store info, toast notifications
├── assets/
│   ├── css/
│   │   └── custom.css      # Custom animations, timeline styling, glowing pulse
│   └── js/
│       └── cart.js         # AJAX cart handler & toast notification engine
├── admin/
│   ├── index.php           # Admin Dashboard (Revenue, Orders, Low stock warnings)
│   ├── orders.php          # Dispatch order queue & status filters
│   ├── order-detail.php    # Live tracking milestone pusher & order inspection
│   ├── products.php        # Inventory manager, add/edit grocery items
│   ├── header.php          # Admin navigation header
│   └── footer.php          # Admin footer
├── index.php               # Homepage with hero banner, category pills & quick tracking widget
├── shop.php                # Grocery catalog with filters, sorting, and organic toggle
├── product-detail.php      # Detailed item view, quantity controls & aisle recommendations
├── cart.php                # Shopping cart with free delivery threshold progress bar
├── cart-action.php         # AJAX/POST cart controller (add, update, remove, clear)
├── checkout.php            # Checkout form & database transaction order placement
├── order-success.php       # Order confirmation receipt with tracking CTA
├── track-order.php         # Live Order Tracker with visual pipeline & activity log
├── orders.php              # Customer's personal order history
├── login.php               # Login page with 1-click demo buttons
├── register.php            # Customer registration
├── logout.php              # Safe session termination
├── .htaccess               # Apache configuration & security rules
└── README.md               # Documentation & setup guide
```

---

## 🛡️ Security & Best Practices
- **Prepared Statements (PDO)**: Eliminates SQL injection risks.
- **XSS Protection**: HTML sanitization applied with `htmlspecialchars` via helper function `e()`.
- **Password Security**: Native bcrypt encryption using `password_hash()` and `password_verify()`.
- **ACID Transactions**: Order placement executes within a database transaction to ensure inventory decrements and order item creation stay consistent.
