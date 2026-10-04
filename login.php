<?php
/**
 * Customer & Admin Login
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

if (is_logged_in()) {
    if (is_admin()) {
        header('Location: admin/index.php');
    } else {
        header('Location: orders.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $db = get_db();
        if (!$db) {
            $error = 'Database connection error. Please run the setup installer.';
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify password using password_verify OR default fallback for pre-seeded users
            $isValid = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $isValid = true;
                } elseif (($email === 'admin@freshmart.com' && $password === 'admin123') ||
                          ($email === 'sarah@freshmart.com' && $password === 'password123')) {
                    $isValid = true;
                }
            }

            if ($isValid) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_phone'] = $user['phone'] ?? '';
                $_SESSION['user_address'] = $user['address'] ?? '';

                set_flash('success', "Welcome back, {$user['name']}!");

                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    $redirect = $_SESSION['redirect_after_login'] ?? 'orders.php';
                    unset($_SESSION['redirect_after_login']);
                    header("Location: $redirect");
                }
                exit;
            } else {
                $error = 'Invalid email or password combination.';
            }
        }
    }
}

$pageTitle = "Login to FreshMart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-14">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-sm">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Sign In</h1>
            <p class="text-xs text-slate-500 mt-1">Access your order tracking and account details</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Quick Demo Credentials Box -->
        <div class="mb-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
            <span class="font-bold text-slate-700 block mb-2">⚡ Quick 1-Click Demo Login:</span>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillCreds('admin@freshmart.com', 'admin123')" class="bg-white hover:bg-slate-100 border border-slate-200 p-2 rounded-xl text-left transition">
                    <span class="block font-bold text-purple-700">Admin Account</span>
                    <span class="text-[10px] text-slate-400">admin@freshmart.com</span>
                </button>
                <button type="button" onclick="fillCreds('sarah@freshmart.com', 'password123')" class="bg-white hover:bg-slate-100 border border-slate-200 p-2 rounded-xl text-left transition">
                    <span class="block font-bold text-emerald-700">Customer Demo</span>
                    <span class="text-[10px] text-slate-400">sarah@freshmart.com</span>
                </button>
            </div>
        </div>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" id="loginEmail" name="email" required value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-slate-700">Password</label>
                </div>
                <input type="password" id="loginPassword" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" name="login" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 text-sm mt-2">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Account
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
            Don't have an account yet? 
            <a href="register.php" class="text-emerald-600 font-bold hover:underline">Create an Account</a>
        </div>
    </div>
</div>

<script>
function fillCreds(email, password) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = password;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
