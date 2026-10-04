<?php
/**
 * Product Detail Page
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = get_db();
$product = null;
$related = [];

if ($db && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product) {
            // Fetch related items from the same category
            $relStmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? LIMIT 4");
            $relStmt->execute([$product['category_id'], $id]);
            $related = $relStmt->fetchAll();
        }
    } catch (Exception $e) {
        $product = null;
    }
}

if (!$product) {
    set_flash('error', 'Grocery item not found.');
    header('Location: shop.php');
    exit;
}

$pageTitle = $product['name'] . " (" . $product['unit'] . ")";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="text-xs text-slate-500 mb-6 flex items-center gap-2">
        <a href="index.php" class="hover:text-emerald-600">Home</a>
        <span>/</span>
        <a href="shop.php" class="hover:text-emerald-600">Aisles</a>
        <span>/</span>
        <a href="shop.php?category=<?= urlencode($product['category_slug']) ?>" class="hover:text-emerald-600"><?= e($product['category_name']) ?></a>
        <span>/</span>
        <span class="text-slate-800 font-semibold"><?= e($product['name']) ?></span>
    </nav>

    <!-- Product Showcase -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-10 shadow-sm mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <!-- Left: Product Image -->
            <div class="lg:col-span-5">
                <div class="relative rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 aspect-square shadow-inner">
                    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
                    
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <?php if ($product['is_organic']): ?>
                            <span class="bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md flex items-center gap-1.5">
                                <i class="fa-solid fa-leaf text-xs"></i> 100% Certified Organic
                            </span>
                        <?php endif; ?>
                        <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                            <span class="bg-amber-500 text-slate-950 text-xs font-extrabold px-3 py-1 rounded-full shadow-md">
                                Special Deal
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right: Product Information & Add to Cart -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block mb-1">
                        <?= e($product['category_name']) ?>
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        <?= e($product['name']) ?>
                    </h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-2.5 py-1 rounded-md">
                            Unit: <?= e($product['unit']) ?>
                        </span>
                        <?php if ($product['stock_qty'] > 0): ?>
                            <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> In Stock (<?= $product['stock_qty'] ?> available)
                            </span>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-rose-500 flex items-center gap-1">
                                <i class="fa-solid fa-circle-xmark"></i> Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Price Row -->
                <div class="flex items-baseline gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-3xl font-extrabold text-slate-900">
                        <?= format_price($product['price']) ?>
                    </span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span class="text-base text-slate-400 line-through">
                            <?= format_price($product['original_price']) ?>
                        </span>
                        <span class="text-xs font-bold text-amber-600 bg-amber-100 px-2 py-0.5 rounded">
                            Save <?= format_price($product['original_price'] - $product['price']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <p class="text-sm text-slate-600 leading-relaxed">
                    <?= nl2br(e($product['description'])) ?>
                </p>

                <!-- Quantity & Add to Cart Form -->
                <?php if ($product['stock_qty'] > 0): ?>
                    <form action="cart-action.php" method="POST" class="pt-2">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                            <!-- Quantity Counter -->
                            <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1 w-36 justify-between">
                                <button type="button" onclick="decrementQty(<?= $product['id'] ?>)" class="w-9 h-9 rounded-lg bg-white shadow-xs text-slate-600 hover:text-emerald-600 font-bold flex items-center justify-center transition">
                                    <i class="fa-solid fa-minus text-xs"></i>
                                </button>
                                <input type="number" id="detailQty" name="quantity" data-qty-product="<?= $product['id'] ?>" value="1" min="1" max="<?= $product['stock_qty'] ?>" class="w-12 text-center bg-transparent text-sm font-bold text-slate-800 focus:outline-none">
                                <button type="button" onclick="incrementQty(<?= $product['id'] ?>, <?= $product['stock_qty'] ?>)" class="w-9 h-9 rounded-lg bg-white shadow-xs text-slate-600 hover:text-emerald-600 font-bold flex items-center justify-center transition">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                </button>
                            </div>

                            <!-- Interactive Add to Cart button (triggers AJAX via cart.js) -->
                            <button type="button" 
                                    class="btn-add-to-cart flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm"
                                    data-product-id="<?= $product['id'] ?>">
                                <i class="fa-solid fa-cart-shopping"></i> Add To Cart
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="p-4 bg-rose-50 text-rose-700 rounded-xl text-sm border border-rose-200">
                        This product is currently out of stock. Check back tomorrow morning after our fresh morning harvest!
                    </div>
                <?php endif; ?>

                <!-- Freshness Guarantee Perks -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-6 border-t border-slate-100 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-emerald-600 text-sm"></i>
                        <span>60-Min Express Dispatch</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-snowflake text-emerald-600 text-sm"></i>
                        <span>Insulated Cold-Chain</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-emerald-600 text-sm"></i>
                        <span>100% Quality Refund</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
        <div class="mt-12">
            <h3 class="text-xl font-bold text-slate-900 mb-6">Similar In This Aisle</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($related as $rel): ?>
                    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg transition flex flex-col group">
                        <div class="relative h-44 bg-slate-50 overflow-hidden">
                            <a href="product-detail.php?id=<?= $rel['id'] ?>" class="block w-full h-full">
                                <img src="<?= e($rel['image_url']) ?>" alt="<?= e($rel['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition">
                            </a>
                            <span class="absolute bottom-2 right-2 bg-white/90 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded shadow-xs">
                                <?= e($rel['unit']) ?>
                            </span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <a href="product-detail.php?id=<?= $rel['id'] ?>" class="font-bold text-slate-900 text-sm hover:text-emerald-600 transition line-clamp-1">
                                    <?= e($rel['name']) ?>
                                </a>
                                <span class="text-base font-extrabold text-slate-900 block mt-1"><?= format_price($rel['price']) ?></span>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-100">
                                <button type="button" 
                                        class="btn-add-to-cart w-full bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white font-bold text-xs py-2 rounded-xl transition flex items-center justify-center gap-1.5"
                                        data-product-id="<?= $rel['id'] ?>">
                                    <i class="fa-solid fa-plus text-xs"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function incrementQty(productId, max) {
    const input = document.getElementById('detailQty');
    let val = parseInt(input.value) || 1;
    if (val < max) {
        input.value = val + 1;
    }
}
function decrementQty(productId) {
    const input = document.getElementById('detailQty');
    let val = parseInt(input.value) || 1;
    if (val > 1) {
        input.value = val - 1;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
