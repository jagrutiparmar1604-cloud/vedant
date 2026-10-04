<?php
/**
 * Order Placement Success & Receipt Confirmation
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

$orderNumber = trim($_GET['order'] ?? '');
$db = get_db();
$order = null;

if ($db && !empty($orderNumber)) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
    } catch (Exception $e) {
        $order = null;
    }
}

if (!$order) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Order Placed Successfully! #" . $order['order_number'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-sm text-center">
        
        <!-- Animated Success Badge -->
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner animate-bounce">
            <i class="fa-solid fa-check"></i>
        </div>

        <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
            Order Confirmed & Received
        </span>

        <h1 class="text-3xl font-extrabold text-slate-900 mt-4 mb-2 tracking-tight">
            Thank you, <?= e($order['customer_name']) ?>!
        </h1>
        <p class="text-sm text-slate-500 max-w-md mx-auto mb-8">
            Your grocery order has been forwarded to our fulfillment warehouse. We're picking and packing your fresh items right now.
        </p>

        <!-- Order Number Highlight Box -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 mb-8 text-left">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
                <div>
                    <span class="text-xs text-slate-400 font-semibold block">Order Reference Number</span>
                    <span class="text-xl sm:text-2xl font-extrabold text-slate-900 font-mono tracking-wider">
                        <?= e($order['order_number']) ?>
                    </span>
                </div>
                <button onclick="navigator.clipboard.writeText('<?= e($order['order_number']) ?>'); showToast('Order ID copied to clipboard!', 'success');" class="self-start sm:self-auto bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-regular fa-copy"></i> Copy Order ID
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 text-xs text-slate-600">
                <div>
                    <span class="text-slate-400 block mb-0.5">Estimated Delivery:</span>
                    <strong class="text-slate-900 font-bold"><?= date('h:i A, M d', strtotime($order['estimated_delivery'])) ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Payment Method:</span>
                    <strong class="text-slate-900 uppercase font-bold"><?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)</strong>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Total Amount:</span>
                    <strong class="text-emerald-700 text-sm font-extrabold"><?= format_price($order['total_amount']) ?></strong>
                </div>
            </div>
        </div>

        <!-- Call to Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="track-order.php?order=<?= urlencode($order['order_number']) ?>" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-truck-fast"></i> Track Order in Real-Time
            </a>
            <a href="shop.php" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3.5 px-6 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                Continue Shopping
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-400">
            A confirmation receipt and tracking link were sent to <span class="text-slate-600 font-semibold"><?= e($order['customer_email']) ?></span>.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
