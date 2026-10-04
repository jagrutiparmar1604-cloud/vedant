<?php
/**
 * Shopping Cart Page
 * FreshMart Online Grocery Store
 */
$pageTitle = "Your Grocery Cart";
require_once __DIR__ . '/includes/header.php';

init_cart();
$cart = $_SESSION['cart'] ?? [];
$subtotal = get_cart_subtotal();
$totals = calculate_order_totals($subtotal);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Shopping Cart</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                <?= count($cart) ?> unique items in your basket
            </p>
        </div>
        <?php if (!empty($cart)): ?>
            <a href="cart-action.php?action=clear" onclick="return confirm('Clear all items from your cart?');" class="text-xs text-rose-600 hover:text-rose-700 font-semibold flex items-center gap-1.5 transition self-start sm:self-auto">
                <i class="fa-regular fa-trash-can"></i> Clear Basket
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($cart)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center max-w-xl mx-auto my-8 shadow-xs">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Your grocery cart is empty</h2>
            <p class="text-sm text-slate-500 mb-6">Looks like you haven't added any farm-fresh produce or pantry staples yet.</p>
            <a href="shop.php" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition">
                <i class="fa-solid fa-store"></i> Browse Fresh Aisles
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Cart Items List (8 Columns) -->
            <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                
                <!-- Free Shipping Progress Tracker -->
                <div class="p-4 sm:p-5 bg-emerald-50/60 border-b border-emerald-100">
                    <?php if ($totals['free_shipping_gap'] > 0): ?>
                        <div class="flex items-center justify-between text-xs font-semibold text-emerald-900 mb-2">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-truck-fast text-emerald-600"></i>
                                Add <strong class="text-emerald-700"><?= format_price($totals['free_shipping_gap']) ?></strong> more to unlock <strong>FREE Express Delivery</strong>!
                            </span>
                            <span><?= round(($totals['subtotal'] / FREE_SHIPPING_THRESHOLD) * 100) ?>%</span>
                        </div>
                        <div class="w-full bg-emerald-200/60 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, round(($totals['subtotal'] / FREE_SHIPPING_THRESHOLD) * 100)) ?>%"></div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                            🎉 Congratulations! You have unlocked FREE Express Delivery on this order.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Items Table / Stack -->
                <div class="divide-y divide-slate-100">
                    <?php foreach ($cart as $item): ?>
                        <?php 
                            $lineTotal = (float)$item['price'] * (int)$item['quantity'];
                        ?>
                        <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            
                            <!-- Thumbnail & Details -->
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-20 h-20 rounded-2xl bg-slate-50 border border-slate-100 overflow-hidden shrink-0">
                                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-sm sm:text-base hover:text-emerald-600 transition">
                                        <a href="product-detail.php?id=<?= $item['id'] ?>"><?= e($item['name']) ?></a>
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-slate-500">Unit: <?= e($item['unit']) ?></span>
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="text-xs font-semibold text-slate-700"><?= format_price($item['price']) ?> / each</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Controls & Line Total -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                                
                                <!-- Quantity modifier -->
                                <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
                                    <a href="cart-action.php?action=update&product_id=<?= $item['id'] ?>&quantity=<?= $item['quantity'] - 1 ?>" class="w-8 h-8 rounded-lg bg-white shadow-xs text-slate-600 hover:text-emerald-600 font-bold flex items-center justify-center transition">
                                        <i class="fa-solid fa-minus text-[10px]"></i>
                                    </a>
                                    <span class="w-10 text-center text-xs font-bold text-slate-800">
                                        <?= $item['quantity'] ?>
                                    </span>
                                    <a href="cart-action.php?action=update&product_id=<?= $item['id'] ?>&quantity=<?= $item['quantity'] + 1 ?>" class="w-8 h-8 rounded-lg bg-white shadow-xs text-slate-600 hover:text-emerald-600 font-bold flex items-center justify-center transition">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                    </a>
                                </div>

                                <!-- Line Subtotal -->
                                <div class="text-right min-w-[70px]">
                                    <span class="block text-sm sm:text-base font-extrabold text-slate-900">
                                        <?= format_price($lineTotal) ?>
                                    </span>
                                </div>

                                <!-- Remove Button -->
                                <a href="cart-action.php?action=remove&product_id=<?= $item['id'] ?>" class="text-slate-400 hover:text-rose-600 transition p-1" title="Remove item">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </a>

                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Footer Actions -->
                <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <a href="shop.php" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary (4 Columns) -->
            <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                <h2 class="text-lg font-bold text-slate-900 pb-3 border-b border-slate-100">Order Summary</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900"><?= format_price($totals['subtotal']) ?></span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span class="flex items-center gap-1.5">
                            Estimated Delivery
                            <?php if ($totals['shipping'] == 0): ?>
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded">FREE</span>
                            <?php endif; ?>
                        </span>
                        <span class="font-bold <?= $totals['shipping'] == 0 ? 'text-emerald-600' : 'text-slate-900' ?>">
                            <?= $totals['shipping'] == 0 ? 'FREE' : format_price($totals['shipping']) ?>
                        </span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Estimated Tax (5%)</span>
                        <span class="font-bold text-slate-900"><?= format_price($totals['tax']) ?></span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-between items-baseline">
                        <span class="text-base font-bold text-slate-900">Total</span>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold text-slate-900"><?= format_price($totals['total']) ?></span>
                            <span class="block text-[10px] text-slate-400">Includes all local produce taxes</span>
                        </div>
                    </div>
                </div>

                <a href="checkout.php" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm">
                    Proceed to Checkout <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>

                <!-- Guarantee bullets -->
                <div class="pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        <span>Secure SSL checkout</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-emerald-600"></i>
                        <span>Choose delivery window at checkout</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-emerald-600"></i>
                        <span>Includes live order tracking link</span>
                    </div>
                </div>

            </div>

        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
