<?php
/**
 * Customer Registration
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

if (is_logged_in()) {
    header('Location: orders.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in your name, email, and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $db = get_db();
        if (!$db) {
            $error = 'Database connection error. Please run the setup wizard.';
        } else {
            // Check if email already registered
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email address already exists. Please login.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $insert = $db->prepare("
                    INSERT INTO users (name, email, password, phone, address, city, role) 
                    VALUES (?, ?, ?, ?, ?, ?, 'customer')
                ");

                try {
                    $insert->execute([$name, $email, $hash, $phone, $address, $city]);
                    $userId = $db->lastInsertId();

                    // Automatic Login
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'customer';
                    $_SESSION['user_phone'] = $phone;
                    $_SESSION['user_address'] = $address;

                    set_flash('success', "Welcome to FreshMart, $name! Your account has been created.");
                    header('Location: shop.php');
                    exit;
                } catch (Exception $e) {
                    $error = 'Failed to create account: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = "Create an Account";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-xl mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-10 shadow-sm">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Create Customer Account</h1>
            <p class="text-xs text-slate-500 mt-1">Enjoy faster checkout, saved delivery addresses, and live order tracking</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Alex Morgan" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="alex@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone</label>
                    <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password (min 6 characters) <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="Choose a strong password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Default Delivery Address</label>
                <input type="text" name="address" value="<?= e($_POST['address'] ?? '') ?>" placeholder="123 Farm View Way, Apt 4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">City / Region</label>
                <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" placeholder="Springfield" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" name="register" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm mt-4">
                <i class="fa-solid fa-user-check"></i> Register Account
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Already have an account? 
            <a href="login.php" class="text-emerald-600 font-bold hover:underline">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
