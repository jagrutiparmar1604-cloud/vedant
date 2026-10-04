<?php
/**
 * Checkout Page & Order Placement Controller
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

init_cart();
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    set_flash('error', 'Your cart is empty. Please add items before proceeding to checkout.');
    header('Location: shop.php');
    exit;
}

$subtotal = get_cart_subtotal();
$totals = calculate_order_totals($subtotal);
$user = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $db = get_db();
    
    if (!$db) {
        $error = 'Database connection error. Please verify your database settings.';
    } else {
        $name = trim($_POST['customer_name'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $address = trim($_POST['delivery_address'] ?? '');
        $city = trim($_POST['delivery_city'] ?? '');
        $notes = trim($_POST['delivery_notes'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';

        // Basic validation
        if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
            $error = 'Please fill in all required delivery fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $db->beginTransaction();

                $orderNumber = generate_order_number();
                $userId = $user ? $user['id'] : null;
                $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';

                // Insert into orders
                $stmt = $db->prepare("
                    INSERT INTO orders (
                        order_number, user_id, customer_name, customer_email, customer_phone, 
                        delivery_address, delivery_city, delivery_notes, subtotal, 
                        shipping_fee, tax_amount, total_amount, payment_method, 
                        payment_status, order_status, estimated_delivery
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?, ?, 
                        ?, ?, ?, ?, 
                        ?, 'placed', DATE_ADD(NOW(), INTERVAL 2 HOUR)
                    )
                ");

                $stmt->execute([
                    $orderNumber,
                    $userId,
                    $name,
                    $email,
                    $phone,
                    $address,
                    $city,
                    $notes,
                    $totals['subtotal'],
                    $totals['shipping'],
                    $totals['tax'],
                    $totals['total'],
                    $paymentMethod,
                    $paymentStatus
                ]);

                $orderId = $db->lastInsertId();

                // Insert Order Items and reduce stock
                $itemStmt = $db->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stockStmt = $db->prepare("
                    UPDATE products SET stock_qty = GREATEST(0, stock_qty - ?) WHERE id = ?
                ");

                foreach ($cart as $item) {
                    $itemSubtotal = (float)$item['price'] * (int)$item['quantity'];
                    $itemStmt->execute([
                        $orderId,
                        $item['id'],
                        $item['name'],
                        $item['price'],
                        $item['quantity'],
                        $itemSubtotal
                    ]);

                    $stockStmt->execute([
                        $item['quantity'],
                        $item['id']
                    ]);
                }

                // Insert initial Tracking Event
                $trackStmt = $db->prepare("
                    INSERT INTO order_tracking (order_id, status, title, description, location) 
                    VALUES (?, 'placed', 'Order Placed & Verified', 'We have received your grocery order. Our fulfillment team is preparing your basket.', 'FreshMart Fulfillment Hub')
                ");
                $trackStmt->execute([$orderId]);

                $db->commit();

                // Clear cart from session
                $_SESSION['cart'] = [];

                // Redirect to Order Success
                header("Location: order-success.php?order=" . urlencode($orderNumber));
                exit;

            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Failed to place order: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = "Secure Checkout";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Delivery & Payment Checkout</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Review your basket and provide delivery coordinates</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Customer Information & Delivery Address (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Delivery Details Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">1. Delivery Address</h2>
                        <p class="text-xs text-slate-500">Where should our fresh courier deliver your groceries?</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_name" required value="<?= e($_POST['customer_name'] ?? ($user['name'] ?? '')) ?>" placeholder="e.g. Sarah Jenkins" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address (for Order Updates & Tracking) <span class="text-rose-500">*</span></label>
                        <input type="email" name="customer_email" required value="<?= e($_POST['customer_email'] ?? ($user['email'] ?? '')) ?>" placeholder="sarah@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone (Driver will call upon arrival) <span class="text-rose-500">*</span></label>
                        <input type="text" name="customer_phone" required value="<?= e($_POST['customer_phone'] ?? ($user['phone'] ?? '')) ?>" placeholder="+1 (555) 234-5678" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Street Address, Apt / Suite <span class="text-rose-500">*</span></label>
                        <input type="text" name="delivery_address" required value="<?= e($_POST['delivery_address'] ?? ($user['address'] ?? '')) ?>" placeholder="452 Maple Grove Blvd, Apt 12" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">City / Region <span class="text-rose-500">*</span></label>
                        <input type="text" name="delivery_city" required value="<?= e($_POST['delivery_city'] ?? 'Springfield') ?>" placeholder="e.g. Springfield" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Special Delivery Instructions (Optional)</label>
                        <textarea name="delivery_notes" rows="2" placeholder="e.g. Please leave package in cooler bag by door / Ring doorbell twice" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"><?= e($_POST['delivery_notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">2. Payment Method</h2>
                        <p class="text-xs text-slate-500">Choose your preferred safe transaction method</p>
                    </div>
                </div>

                <div class="space-y-3">
                    
                    <!-- Cash on Delivery -->
                    <label class="flex items-center justify-between p-4 border rounded-2xl cursor-pointer hover:border-emerald-500 transition border-emerald-500 bg-emerald-50/30">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="cod" checked class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block font-bold text-sm text-slate-800">Cash on Delivery (COD)</span>
                                <span class="block text-xs text-slate-500">Pay cash or card to driver upon package handover</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-money-bill-wave text-emerald-600 text-xl"></i>
                    </label>

                    <!-- Credit / Debit Card Mock -->
                    <label class="flex items-center justify-between p-4 border border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="card" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block font-bold text-sm text-slate-800">Credit or Debit Card</span>
                                <span class="block text-xs text-slate-500">Visa, Mastercard, Amex (Instant test verification)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 text-slate-400 text-lg">
                            <i class="fa-brands fa-cc-visa"></i>
                            <i class="fa-brands fa-cc-mastercard"></i>
                        </div>
                    </label>

                    <!-- Instant UPI / QR -->
                    <label class="flex items-center justify-between p-4 border border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="upi" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block font-bold text-sm text-slate-800">Instant UPI / QR Code</span>
                                <span class="block text-xs text-slate-500">Scan and pay using any UPI app</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-qrcode text-slate-400 text-xl"></i>
                    </label>

                </div>
            </div>

        </div>

        <!-- Right: Order Items & Placement Summary (4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <h2 class="text-lg font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center justify-between">
                <span>Items in Order</span>
                <span class="text-xs font-semibold text-emerald-600"><?= count($cart) ?> items</span>
            </h2>

            <!-- Scrollable Mini Items List -->
            <div class="max-h-60 overflow-y-auto space-y-3 pr-2">
                <?php foreach ($cart as $item): ?>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-10 h-10 rounded-lg object-cover bg-slate-50 border">
                            <div>
                                <h4 class="font-bold text-slate-800 line-clamp-1"><?= e($item['name']) ?></h4>
                                <span class="text-slate-400"><?= $item['quantity'] ?> &times; <?= format_price($item['price']) ?></span>
                            </div>
                        </div>
                        <span class="font-bold text-slate-900">
                            <?= format_price((float)$item['price'] * (int)$item['quantity']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-2.5 text-xs text-slate-600 pt-4 border-t border-slate-100">
                <div class="flex justify-between">
                    <span>Groceries Subtotal</span>
                    <span class="font-bold text-slate-900"><?= format_price($totals['subtotal']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Express Delivery</span>
                    <span class="font-bold <?= $totals['shipping'] == 0 ? 'text-emerald-600' : 'text-slate-900' ?>">
                        <?= $totals['shipping'] == 0 ? 'FREE' : format_price($totals['shipping']) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Sales Tax (5%)</span>
                    <span class="font-bold text-slate-900"><?= format_price($totals['tax']) ?></span>
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-between items-baseline text-sm">
                    <span class="font-bold text-slate-900">Grand Total</span>
                    <span class="text-2xl font-extrabold text-slate-900"><?= format_price($totals['total']) ?></span>
                </div>
            </div>

            <!-- Place Order Button -->
            <button type="submit" name="place_order" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-lock"></i> Place Order (<?= format_price($totals['total']) ?>)
            </button>

            <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                By clicking "Place Order", you'll receive an instant <strong>Order Reference Number</strong> and live access to our step-by-step courier dispatch tracker.
            </p>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
