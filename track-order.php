<?php
/**
 * Live Order Tracking Page
 * FreshMart Online Grocery Store
 */
$pageTitle = "Live Order Tracking";
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$orderNumber = trim($_GET['order'] ?? '');
$contact = trim($_GET['contact'] ?? '');

$order = null;
$orderItems = [];
$trackingEvents = [];
$searchAttempted = !empty($orderNumber);
$errorMessage = '';

if ($searchAttempted) {
    if (!$db) {
        $errorMessage = "Database connection error. Please run the setup installer.";
    } else {
        try {
            // Find order
            $query = "SELECT * FROM orders WHERE order_number = ?";
            $params = [$orderNumber];

            if (!empty($contact)) {
                $query .= " AND (customer_phone = ? OR customer_email = ?)";
                $params[] = $contact;
                $params[] = $contact;
            }

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $order = $stmt->fetch();

            if ($order) {
                // Fetch Order Items
                $itemStmt = $db->prepare("
                    SELECT oi.*, p.image_url, p.unit 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $itemStmt->execute([$order['id']]);
                $orderItems = $itemStmt->fetchAll();

                // Fetch Tracking Events (newest first or oldest first)
                $trackStmt = $db->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC");
                $trackStmt->execute([$order['id']]);
                $trackingEvents = $trackStmt->fetchAll();
            } else {
                $errorMessage = "We couldn't locate any order matching '#$orderNumber'" . (!empty($contact) ? " with the provided contact information." : ".");
            }
        } catch (Exception $e) {
            $errorMessage = "Query error: " . $e->getMessage();
        }
    }
}

// Pipeline mapping for the 5 linear progress steps
$pipelineSteps = [
    'placed' => ['name' => 'Order Placed', 'icon' => 'fa-receipt', 'num' => 1],
    'processing' => ['name' => 'Processing', 'icon' => 'fa-clipboard-check', 'num' => 2],
    'packed' => ['name' => 'Packed', 'icon' => 'fa-box-open', 'num' => 3],
    'out_for_delivery' => ['name' => 'Out for Delivery', 'icon' => 'fa-truck-fast', 'num' => 4],
    'delivered' => ['name' => 'Delivered', 'icon' => 'fa-house-circle-check', 'num' => 5],
];

$currentStatusKey = $order ? $order['order_status'] : 'placed';
$currentStepNum = $pipelineSteps[$currentStatusKey]['num'] ?? 1;
if ($currentStatusKey === 'cancelled') {
    $currentStepNum = 0;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Top Search / Lookup Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs mb-8">
        <div class="max-w-2xl mx-auto text-center mb-6">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fa-solid fa-route"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Real-Time Grocery Tracker</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Enter your Order Reference Number to view live preparation and delivery coordinates.
            </p>
        </div>

        <form method="GET" action="track-order.php" class="max-w-2xl mx-auto">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="order" value="<?= e($orderNumber) ?>" placeholder="Order Reference (e.g. FM-20261004-9842)" required class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    <i class="fa-solid fa-receipt absolute left-4 top-3.5 text-slate-400"></i>
                </div>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-xl transition duration-150 shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 text-sm shrink-0">
                    <i class="fa-solid fa-magnifying-glass"></i> Track Status
                </button>
            </div>

            <!-- Demo pill -->
            <div class="mt-3 text-center text-xs text-slate-500 flex items-center justify-center gap-2">
                <span>Want to test tracking right away?</span>
                <a href="track-order.php?order=FM-20261004-9842" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                    Click to load demo order <code class="bg-emerald-50 px-1 py-0.5 rounded text-emerald-800">FM-20261004-9842</code>
                </a>
            </div>
        </form>

        <?php if (!empty($errorMessage)): ?>
            <div class="max-w-2xl mx-auto mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-500 mt-0.5"></i>
                <div>
                    <strong>Order Not Found</strong>
                    <p class="mt-0.5"><?= e($errorMessage) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($order): ?>
        
        <!-- Order Header Information -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                        <span class="text-xs font-semibold text-slate-400">Order Reference:</span>
                        <span class="text-xl sm:text-2xl font-mono font-extrabold text-slate-900 tracking-wider">
                            <?= e($order['order_number']) ?>
                        </span>
                        
                        <?php 
                            $statusInfo = ORDER_STATUSES[$order['order_status']] ?? [
                                'label' => ucfirst($order['order_status']),
                                'badge' => 'bg-slate-100 text-slate-800 border-slate-200'
                            ];
                        ?>
                        <span class="text-xs font-bold px-3 py-1 rounded-full border <?= $statusInfo['badge'] ?>">
                            <?= e($statusInfo['label']) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        Placed on <strong class="text-slate-700"><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></strong> &bull; Delivering to <strong><?= e($order['customer_name']) ?></strong>
                    </p>
                </div>

                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 sm:p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Estimated Delivery</span>
                        <span class="text-base sm:text-lg font-extrabold text-emerald-800">
                            <?= date('h:i A (Today)', strtotime($order['estimated_delivery'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- VISUAL STEP-BY-STEP TRACKER PIPELINE -->
            <div class="pt-8 pb-4">
                <?php if ($order['order_status'] === 'cancelled'): ?>
                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-center text-rose-800">
                        <i class="fa-solid fa-circle-xmark text-3xl text-rose-500 mb-2"></i>
                        <h3 class="text-lg font-bold">This Order Has Been Cancelled</h3>
                        <p class="text-xs text-rose-600 mt-1">If you have questions or need assistance, please contact FreshMart Customer Support.</p>
                    </div>
                <?php else: ?>
                    
                    <!-- Desktop Progress Steps -->
                    <div class="relative">
                        <!-- Progress Background Bar -->
                        <div class="absolute top-1/2 left-0 right-0 h-1.5 -translate-y-1/2 bg-slate-100 rounded-full z-0 hidden md:block">
                            <?php 
                                $percentComplete = (($currentStepNum - 1) / 4) * 100;
                            ?>
                            <div class="h-1.5 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-700" style="width: <?= $percentComplete ?>%"></div>
                        </div>

                        <!-- Step Circles Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 md:gap-2 relative z-10">
                            <?php foreach ($pipelineSteps as $key => $step): ?>
                                <?php 
                                    $isPast = $currentStepNum > $step['num'];
                                    $isCurrent = $currentStepNum === $step['num'];
                                    $isFuture = $currentStepNum < $step['num'];
                                ?>
                                <div class="flex md:flex-col items-center gap-4 md:gap-2 text-left md:text-center">
                                    
                                    <!-- Step Icon Bubble -->
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-base font-bold transition-all duration-300 shrink-0 shadow-xs <?= 
                                        $isPast ? 'bg-emerald-600 text-white' : 
                                        ($isCurrent ? 'bg-emerald-600 text-white pulse-active-step ring-4 ring-emerald-200' : 'bg-slate-100 text-slate-400') 
                                    ?>">
                                        <?php if ($isPast): ?>
                                            <i class="fa-solid fa-check"></i>
                                        <?php else: ?>
                                            <i class="fa-solid <?= $step['icon'] ?>"></i>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Step Title & State -->
                                    <div>
                                        <h4 class="text-xs font-bold <?= $isCurrent ? 'text-emerald-700' : ($isPast ? 'text-slate-800' : 'text-slate-400') ?>">
                                            <?= $step['name'] ?>
                                        </h4>
                                        <span class="text-[10px] block <?= $isCurrent ? 'text-emerald-600 font-semibold' : 'text-slate-400' ?>">
                                            <?= $isCurrent ? 'In Progress' : ($isPast ? 'Completed' : 'Pending') ?>
                                        </span>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <!-- 2-Column Section: Tracking Event Timeline & Order Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Live Activity Log (7 Cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-600"></i>
                        <h2 class="text-base font-bold text-slate-900">Live Delivery Activity Log</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Automatic updates</span>
                </div>

                <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    <?php if (empty($trackingEvents)): ?>
                        <p class="text-xs text-slate-400 italic">No milestone logs recorded yet.</p>
                    <?php else: ?>
                        <?php foreach (array_reverse($trackingEvents) as $idx => $ev): ?>
                            <div class="relative group">
                                <!-- Dot -->
                                <div class="absolute -left-[27px] top-1 w-4 h-4 rounded-full border-2 border-white <?= $idx === 0 ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-slate-400' ?>"></div>
                                
                                <div class="bg-slate-50 hover:bg-emerald-50/40 border border-slate-100 rounded-2xl p-4 transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                        <h3 class="font-bold text-sm text-slate-900"><?= e($ev['title']) ?></h3>
                                        <time class="text-[11px] text-slate-400 font-medium">
                                            <?= date('M d, g:i A', strtotime($ev['created_at'])) ?>
                                        </time>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed"><?= e($ev['description']) ?></p>
                                    
                                    <?php if (!empty($ev['location'])): ?>
                                        <div class="mt-2.5 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700">
                                            <i class="fa-solid fa-location-dot text-xs text-emerald-500"></i>
                                            <span><?= e($ev['location']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Courier Notes -->
                <div class="mt-8 p-4 bg-teal-50/60 border border-teal-100 rounded-2xl flex items-start gap-3 text-xs text-teal-900">
                    <i class="fa-solid fa-snowflake text-teal-600 text-sm mt-0.5"></i>
                    <div>
                        <strong class="font-bold text-teal-950">Cold-Chain Temperature Certified:</strong>
                        <p class="mt-0.5 text-teal-800">All perishables, dairy, and produce in this order are maintained below 4&deg;C (39&deg;F) until handoff.</p>
                    </div>
                </div>
            </div>

            <!-- Right: Order Details, Delivery Address & Items (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Destination Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Delivery Destination
                    </h3>

                    <div class="space-y-3 text-xs text-slate-600">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Customer:</span>
                            <span class="font-bold text-slate-900 text-sm"><?= e($order['customer_name']) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Address:</span>
                            <span class="font-medium text-slate-800"><?= e($order['delivery_address']) ?>, <?= e($order['delivery_city']) ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Contact:</span>
                            <span class="font-medium text-slate-800"><?= e($order['customer_phone']) ?> &bull; <?= e($order['customer_email']) ?></span>
                        </div>
                        <?php if (!empty($order['delivery_notes'])): ?>
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-amber-900">
                                <strong>Delivery instructions:</strong> <?= e($order['delivery_notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Items in this Order -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-basket-shopping text-emerald-600"></i> Items (<?= count($orderItems) ?>)
                        </span>
                        <span class="text-xs text-slate-400 font-normal"><?= format_price($order['total_amount']) ?></span>
                    </h3>

                    <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto pr-1 space-y-2">
                        <?php foreach ($orderItems as $it): ?>
                            <div class="py-2.5 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-50 border overflow-hidden shrink-0">
                                        <img src="<?= e($it['image_url'] ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=100') ?>" alt="<?= e($it['product_name']) ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 line-clamp-1"><?= e($it['product_name']) ?></h4>
                                        <span class="text-slate-400"><?= $it['quantity'] ?> &times; <?= format_price($it['price']) ?></span>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900"><?= format_price($it['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Payment summary -->
                    <div class="pt-4 mt-4 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-bold text-slate-900"><?= format_price($order['subtotal']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Delivery:</span>
                            <span class="font-bold <?= $order['shipping_fee'] == 0 ? 'text-emerald-600' : 'text-slate-900' ?>">
                                <?= $order['shipping_fee'] == 0 ? 'FREE' : format_price($order['shipping_fee']) ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tax:</span>
                            <span class="font-bold text-slate-900"><?= format_price($order['tax_amount']) ?></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t font-bold text-slate-900 text-sm">
                            <span>Total (<?= strtoupper(e($order['payment_method'])) ?>):</span>
                            <span class="text-emerald-700"><?= format_price($order['total_amount']) ?></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
