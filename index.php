<?php
/**
 * Homepage
 * FreshMart Online Grocery Store
 */
$pageTitle = "Online Grocery Store - Fresh Produce & Fast Delivery";
require_once __DIR__ . '/includes/header.php';

$db = get_db();
$categories = [];
$featuredProducts = [];
$organicProducts = [];

if ($db) {
    try {
        // Fetch Categories
        $catStmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
        $categories = $catStmt->fetchAll();

        // Fetch Featured Products
        $featStmt = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 ORDER BY p.id ASC LIMIT 8");
        $featuredProducts = $featStmt->fetchAll();

        // Fetch Organic Picks
        $orgStmt = $db->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_organic = 1 ORDER BY p.id DESC LIMIT 4");
        $organicProducts = $orgStmt->fetchAll();
    } catch (Exception $e) {
        // fallback if db has issues
    }
}
?>

<!-- Hero Section with Grocery Delivery Banner & Quick Track Order Widget -->
<section class="relative bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-900 text-white overflow-hidden py-16 md:py-20">
    <!-- Background subtle organic pattern -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-400/30 px-3.5 py-1.5 rounded-full text-emerald-200 text-xs font-semibold backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Fast 60-Minute Doorstep Delivery Available
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                    Eat Fresh, Live Healthy. <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-200 to-amber-200">
                        Groceries To Your Door.
                    </span>
                </h1>
                
                <p class="text-emerald-100/90 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed font-normal">
                    Crisp organic fruits, seasonal vegetables, farmstead milk, and artisanal bakery staples harvested daily from local growers.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="shop.php" class="w-full sm:w-auto bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-8 py-3.5 rounded-xl shadow-lg shadow-emerald-500/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-basket-shopping"></i> Start Shopping Fresh
                    </a>
                    <a href="#track-section" class="w-full sm:w-auto bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold px-6 py-3.5 rounded-xl backdrop-blur-sm transition flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-truck-fast text-emerald-300"></i> Track Existing Order
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="pt-6 border-t border-emerald-700/50 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-emerald-200 font-medium">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400"></i> Zero chemical pesticides</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400"></i> Chilled cold-chain packaging</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-check text-emerald-400"></i> Live GPS courier tracking</span>
                </div>
            </div>

            <!-- Right Hero Card: Quick Order Tracker Box -->
            <div class="lg:col-span-5" id="track-section">
                <div class="bg-white rounded-3xl p-6 sm:p-8 text-slate-800 shadow-2xl border border-white/20 relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-route"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Track Your Order</h3>
                                <p class="text-xs text-slate-500">Live grocery dispatch updates</p>
                            </div>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2.5 py-1 rounded-full">
                            Real-time
                        </span>
                    </div>

                    <form action="track-order.php" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Order Reference Number</label>
                            <div class="relative">
                                <input type="text" name="order" placeholder="e.g. FM-20261004-9842" required class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                                <i class="fa-solid fa-receipt absolute left-3.5 top-3 text-slate-400 text-sm"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number or Email (Optional verification)</label>
                            <div class="relative">
                                <input type="text" name="contact" placeholder="Phone or email on file" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-user-check absolute left-3.5 top-3 text-slate-400 text-sm"></i>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition duration-150 shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 text-sm">
                            <i class="fa-solid fa-magnifying-glass-location"></i> Track Package Now
                        </button>
                    </form>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Have a demo code?</span>
                        <a href="track-order.php?order=FM-20261004-9842" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                            Try FM-20261004-9842 &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Shop By Categories -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
        <div>
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Aisle Navigation</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Explore Categories</h2>
        </div>
        <a href="shop.php" class="text-sm font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1.5 transition">
            Browse All Aisles <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <?php foreach ($categories as $cat): ?>
            <a href="shop.php?category=<?= urlencode($cat['slug']) ?>" class="group bg-white p-4 rounded-2xl border border-slate-200/80 hover:border-emerald-500 hover:shadow-lg transition duration-200 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl overflow-hidden mb-3 bg-slate-100 relative shadow-xs">
                    <img src="<?= e($cat['image_url']) ?>" alt="<?= e($cat['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                    <div class="absolute inset-0 bg-slate-900/10 group-hover:bg-transparent transition"></div>
                </div>
                <h4 class="font-bold text-xs text-slate-800 group-hover:text-emerald-600 transition"><?= e($cat['name']) ?></h4>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Grocery Products Grid -->
<section class="bg-slate-100/60 py-14 border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Daily Market Selection</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Today's Fresh Picks</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="shop.php?featured=1" class="text-xs font-bold bg-white text-slate-700 border border-slate-200 px-3 py-1.5 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition">
                    View All Featured
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $item): ?>
                <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col group">
                    <!-- Image with Badges -->
                    <div class="relative h-48 bg-slate-50 overflow-hidden">
                        <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        
                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                            <?php if ($item['is_organic']): ?>
                                <span class="bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                    <i class="fa-solid fa-leaf text-[9px]"></i> Organic
                                </span>
                            <?php endif; ?>
                            <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                <?php 
                                    $discount = round((($item['original_price'] - $item['price']) / $item['original_price']) * 100);
                                ?>
                                <span class="bg-amber-500 text-slate-900 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-xs">
                                    <?= $discount ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Unit pill -->
                        <span class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-xs text-slate-700 text-[11px] font-semibold px-2.5 py-1 rounded-md shadow-xs">
                            <?= e($item['unit']) ?>
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block mb-1">
                                <?= e($item['category_name']) ?>
                            </span>
                            <a href="product-detail.php?id=<?= $item['id'] ?>" class="font-bold text-slate-900 text-base hover:text-emerald-600 transition line-clamp-1">
                                <?= e($item['name']) ?>
                            </a>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                <?= e($item['description']) ?>
                            </p>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-lg font-extrabold text-slate-900"><?= format_price($item['price']) ?></span>
                                <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                    <span class="text-xs text-slate-400 line-through ml-1"><?= format_price($item['original_price']) ?></span>
                                <?php endif; ?>
                            </div>

                            <button type="button" 
                                    class="btn-add-to-cart bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white font-bold text-xs px-3.5 py-2 rounded-xl transition duration-150 flex items-center gap-1.5 border border-emerald-200 hover:border-emerald-600 active:scale-95"
                                    data-product-id="<?= $item['id'] ?>">
                                <i class="fa-solid fa-plus text-xs"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Visual Order Tracking Featurette -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-gradient-to-r from-teal-900 via-emerald-900 to-slate-900 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <div class="lg:col-span-6 space-y-4">
                <span class="bg-emerald-500/20 text-emerald-300 font-bold text-xs uppercase px-3 py-1 rounded-full border border-emerald-400/20">
                    Live Grocery Dispatch
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Know Exactly When Your Groceries Arrive</h2>
                <p class="text-emerald-100/80 text-sm leading-relaxed">
                    Unlike standard package shipping, perishable food requires precision. FreshMart tracks every temperature-controlled stage: from farm picking to sorting, packing in thermal ice bags, and live courier van dispatch.
                </p>

                <!-- Interactive 5-step preview -->
                <div class="pt-4 grid grid-cols-5 gap-2 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-900 font-bold flex items-center justify-center text-xs mb-1">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] text-emerald-200 font-medium">Placed</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-900 font-bold flex items-center justify-center text-xs mb-1">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] text-emerald-200 font-medium">Processing</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-900 font-bold flex items-center justify-center text-xs mb-1">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <span class="text-[10px] text-emerald-200 font-medium">Packed</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-amber-400 text-slate-950 font-bold flex items-center justify-center text-xs mb-1 pulse-active-step">
                            <i class="fa-solid fa-van-shuttle"></i>
                        </div>
                        <span class="text-[10px] text-amber-300 font-bold">On The Way</span>
                    </div>
                    <div class="flex flex-col items-center opacity-40">
                        <div class="w-8 h-8 rounded-full bg-slate-700 text-slate-300 flex items-center justify-center text-xs mb-1">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">Delivered</span>
                    </div>
                </div>

                <div class="pt-4">
                    <a href="track-order.php?order=FM-20261004-9842" class="inline-flex items-center gap-2 bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-bold text-xs px-5 py-3 rounded-xl transition">
                        <i class="fa-solid fa-eye"></i> View Live Demo Tracking Experience &rarr;
                    </a>
                </div>
            </div>

            <div class="lg:col-span-6 flex justify-center">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                        <div>
                            <span class="text-xs text-emerald-300 font-mono">FM-20261004-9842</span>
                            <div class="text-sm font-bold text-white mt-0.5">David M. (Driver #04)</div>
                        </div>
                        <span class="bg-indigo-500/30 text-indigo-200 border border-indigo-400/30 text-[10px] font-bold px-2.5 py-1 rounded-full">
                            In Transit (45 mins)
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3 text-emerald-200">
                            <i class="fa-solid fa-circle text-[8px] text-emerald-400 mt-1"></i>
                            <div>
                                <span class="font-bold text-white">Out for Delivery</span>
                                <p class="text-slate-300 text-[11px]">Van en route to 742 Evergreen Terrace</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 text-slate-300">
                            <i class="fa-solid fa-circle text-[8px] text-slate-400 mt-1"></i>
                            <div>
                                <span class="font-medium text-slate-200">Cold Sealed & Packed</span>
                                <p class="text-slate-400 text-[11px]">Dispatch Bay 3 &bull; Quality certified</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
