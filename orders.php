<?php
/**
 * Customer Order History
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

require_login('login.php');

$pageTitle = "My Grocery Orders";
require_once __DIR__ . '/includes/header.php';

$user = current_user();
$db = get_db();
$orders = [];

if ($db) {
    try {
        $stmt = $db->prepare("
            SELECT o.*, COUNT(oi.id) as item_count 
            FROM orders o 
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.user_id = ? 
            GROUP BY o.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        $orders = [];
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">My Order History</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Signed in as <strong class="text-slate-800"><?= e($user['name']) ?></strong> (<?= e($user['email']) ?>)
            </p>
        </div>
        <a href="shop.php" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-2 self-start sm:self-auto shadow-xs">
            <i class="fa-solid fa-cart-plus"></i> Shop Fresh Groceries
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-lg mx-auto shadow-xs">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-3xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">No orders placed yet</h3>
            <p class="text-sm text-slate-500 mb-6">Your fresh grocery basket awaits! Explore our fresh produce, dairy, bakery, and pantry aisles.</p>
            <a href="shop.php" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-5 py-3 rounded-xl transition">
                Start Shopping Now
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($orders as $ord): ?>
                <?php 
                    $statusInfo = ORDER_STATUSES[$ord['order_status']] ?? [
                        'label' => ucfirst($ord['order_status']),
                        'badge' => 'bg-slate-100 text-slate-800 border-slate-200'
                    ];
                ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs hover:border-emerald-500/50 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-base text-slate-900"><?= e($ord['order_number']) ?></span>
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border <?= $statusInfo['badge'] ?>">
                                        <?= e($statusInfo['label']) ?>
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400">Placed on <?= date('M d, Y \a\t g:i A', strtotime($ord['created_at'])) ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="track-order.php?order=<?= urlencode($ord['order_number']) ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-xs">
                                <i class="fa-solid fa-truck-fast"></i> Track Order Live
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs text-slate-600">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Items</span>
                            <strong class="text-slate-800 font-semibold"><?= $ord['item_count'] ?> Products</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Total Amount</span>
                            <strong class="text-emerald-700 font-extrabold text-sm"><?= format_price($ord['total_amount']) ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Payment</span>
                            <span class="uppercase font-semibold text-slate-800"><?= e($ord['payment_method']) ?> (<?= e($ord['payment_status']) ?>)</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Destination</span>
                            <span class="text-slate-800 truncate block"><?= e($ord['delivery_address']) ?>, <?= e($ord['delivery_city']) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
