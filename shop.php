<?php
/**
 * Shop / Catalog Page
 * FreshMart Online Grocery Store
 */
$pageTitle = "Fresh Grocery Catalog & Supermarket Aisles";
require_once __DIR__ . '/includes/header.php';

$db = get_db();

// Filter parameters
$search = trim($_GET['search'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$organicOnly = isset($_GET['organic']) && $_GET['organic'] == '1';
$featuredOnly = isset($_GET['featured']) && $_GET['featured'] == '1';
$sort = $_GET['sort'] ?? 'featured';

$categories = [];
$products = [];
$currentCategory = null;

if ($db) {
    try {
        // Fetch all categories for filter sidebar
        $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

        // Build dynamic query
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        $params = [];

        if ($categorySlug !== '') {
            $sql .= " AND c.slug = ?";
            $params[] = $categorySlug;

            foreach ($categories as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $currentCategory = $cat;
                    break;
                }
            }
        }

        if ($search !== '') {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($organicOnly) {
            $sql .= " AND p.is_organic = 1";
        }

        if ($featuredOnly) {
            $sql .= " AND p.is_featured = 1";
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.name ASC";
                break;
            case 'newest':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
                break;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

    } catch (Exception $e) {
        $products = [];
    }
}
?>

<!-- Shop Page Container -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb & Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="text-xs text-slate-500 mb-1 flex items-center gap-1.5">
                <a href="index.php" class="hover:text-emerald-600">Home</a>
                <span>/</span>
                <a href="shop.php" class="hover:text-emerald-600">Aisles</a>
                <?php if ($currentCategory): ?>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold"><?= e($currentCategory['name']) ?></span>
                <?php endif; ?>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                <?= $currentCategory ? e($currentCategory['name']) : ($search ? 'Search results for "' . e($search) . '"' : 'All Grocery Aisles') ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Showing <?= count($products) ?> fresh produce & pantry items
            </p>
        </div>

        <!-- Sort & Filter Pills -->
        <div class="flex flex-wrap items-center gap-2">
            <form id="sortForm" method="GET" class="flex items-center gap-2">
                <?php if ($categorySlug): ?><input type="hidden" name="category" value="<?= e($categorySlug) ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                <?php if ($organicOnly): ?><input type="hidden" name="organic" value="1"><?php endif; ?>

                <label class="text-xs font-semibold text-slate-500">Sort by:</label>
                <select name="sort" onchange="document.getElementById('sortForm').submit()" class="bg-white border border-slate-200 text-xs font-semibold text-slate-700 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>Featured & Popular</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A - Z</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Harvest</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Main Layout: Sidebar & Products Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Filter Sidebar -->
        <aside class="lg:col-span-1 space-y-6">
            
            <!-- Category Filter Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <h3 class="font-bold text-sm text-slate-900 mb-3 flex items-center justify-between">
                    <span>Aisle Categories</span>
                    <?php if ($categorySlug): ?>
                        <a href="shop.php" class="text-xs font-medium text-emerald-600 hover:underline">Reset</a>
                    <?php endif; ?>
                </h3>
                <ul class="space-y-1.5 text-sm">
                    <li>
                        <a href="shop.php<?= $organicOnly ? '?organic=1' : '' ?>" class="flex items-center justify-between px-3 py-2 rounded-xl transition <?= empty($categorySlug) ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 hover:bg-slate-50' ?>">
                            <span>All Aisles</span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="shop.php?category=<?= urlencode($cat['slug']) ?><?= $organicOnly ? '&organic=1' : '' ?>" class="flex items-center justify-between px-3 py-2 rounded-xl transition <?= ($categorySlug === $cat['slug']) ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 hover:bg-slate-50' ?>">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid <?= e($cat['icon']) ?> text-xs text-slate-400"></i>
                                    <?= e($cat['name']) ?>
                                </span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Quick Filter Preferences -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
                <h3 class="font-bold text-sm text-slate-900 mb-3">Dietary & Harvest</h3>
                <div class="space-y-2">
                    <a href="shop.php?<?= http_build_query(array_merge($_GET, ['organic' => $organicOnly ? 0 : 1])) ?>" class="flex items-center justify-between p-2.5 rounded-xl border transition <?= $organicOnly ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                        <span class="flex items-center gap-2 text-xs">
                            <i class="fa-solid fa-leaf text-emerald-600"></i> 100% Certified Organic
                        </span>
                        <input type="checkbox" <?= $organicOnly ? 'checked' : '' ?> class="rounded text-emerald-600 pointer-events-none">
                    </a>

                    <a href="shop.php?<?= http_build_query(array_merge($_GET, ['featured' => $featuredOnly ? 0 : 1])) ?>" class="flex items-center justify-between p-2.5 rounded-xl border transition <?= $featuredOnly ? 'border-amber-500 bg-amber-50/50 text-amber-900 font-bold' : 'border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                        <span class="flex items-center gap-2 text-xs">
                            <i class="fa-solid fa-star text-amber-500"></i> Daily Specials & Deals
                        </span>
                        <input type="checkbox" <?= $featuredOnly ? 'checked' : '' ?> class="rounded text-amber-500 pointer-events-none">
                    </a>
                </div>
            </div>

            <!-- Order Tracking Promo Box in Shop -->
            <div class="bg-gradient-to-br from-emerald-800 to-teal-900 rounded-2xl p-5 text-white shadow-md">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-300 text-lg mb-3">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <h4 class="font-bold text-sm">Where's my order?</h4>
                <p class="text-xs text-emerald-100/80 mt-1 mb-4">Track any active delivery with real-time status & route alerts.</p>
                <a href="track-order.php" class="block w-full text-center bg-white text-emerald-900 font-bold text-xs py-2 px-3 rounded-xl hover:bg-emerald-50 transition">
                    Open Order Tracker
                </a>
            </div>

        </aside>

        <!-- Product Grid -->
        <main class="lg:col-span-3">
            <?php if (empty($products)): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-carrot"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">No groceries found</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">We couldn't find any items matching your selected criteria. Try adjusting your search or aisle filter.</p>
                    <a href="shop.php" class="inline-flex items-center gap-2 bg-emerald-600 text-white font-semibold text-xs px-5 py-2.5 rounded-xl hover:bg-emerald-700 transition">
                        View All Products
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $item): ?>
                        <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col group">
                            
                            <!-- Product Image -->
                            <div class="relative h-48 bg-slate-50 overflow-hidden">
                                <a href="product-detail.php?id=<?= $item['id'] ?>" class="block w-full h-full">
                                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </a>

                                <!-- Badges -->
                                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                    <?php if ($item['is_organic']): ?>
                                        <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                            <i class="fa-solid fa-leaf text-[9px]"></i> Organic
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                        <?php $pct = round((($item['original_price'] - $item['price']) / $item['original_price']) * 100); ?>
                                        <span class="bg-amber-500 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs">
                                            <?= $pct ?>% OFF
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <span class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-xs text-slate-700 text-[11px] font-semibold px-2 py-0.5 rounded-md shadow-xs">
                                    <?= e($item['unit']) ?>
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block mb-1">
                                        <?= e($item['category_name']) ?>
                                    </span>
                                    <a href="product-detail.php?id=<?= $item['id'] ?>" class="font-bold text-slate-900 text-sm sm:text-base hover:text-emerald-600 transition line-clamp-1">
                                        <?= e($item['name']) ?>
                                    </a>
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                        <?= e($item['description']) ?>
                                    </p>
                                </div>

                                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-base sm:text-lg font-extrabold text-slate-900"><?= format_price($item['price']) ?></span>
                                        <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                            <span class="text-xs text-slate-400 line-through ml-1"><?= format_price($item['original_price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($item['stock_qty'] > 0): ?>
                                        <button type="button" 
                                                class="btn-add-to-cart bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white font-bold text-xs px-3.5 py-2 rounded-xl transition duration-150 flex items-center gap-1.5 border border-emerald-200 hover:border-emerald-600 active:scale-95"
                                                data-product-id="<?= $item['id'] ?>">
                                            <i class="fa-solid fa-plus text-xs"></i> Add
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">Out of stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
