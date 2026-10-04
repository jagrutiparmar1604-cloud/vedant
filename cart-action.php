<?php
/**
 * Shopping Cart Actions Handler
 * Supports both AJAX JSON calls and standard Form POST submissions
 */
require_once __DIR__ . '/config/db.php';

init_cart();

// Detect if this is an AJAX/Fetch request
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
          (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
          isset($_REQUEST['ajax']);

$action = $_REQUEST['action'] ?? '';
$productId = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$quantity = isset($_REQUEST['quantity']) ? (int)$_REQUEST['quantity'] : 1;

$response = [
    'success' => false,
    'message' => 'Invalid action',
    'cart_count' => get_cart_count(),
    'cart_subtotal' => get_cart_subtotal()
];

$db = get_db();

if ($action === 'add' && $productId > 0) {
    if (!$db) {
        $response['message'] = 'Database connection error';
    } else {
        $stmt = $db->prepare("SELECT id, name, slug, price, original_price, unit, stock_qty, image_url FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $response['message'] = 'Product not found.';
        } elseif ($product['stock_qty'] <= 0) {
            $response['message'] = 'Sorry, this product is currently out of stock!';
        } else {
            $existingQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId]['quantity'] : 0;
            $newQty = $existingQty + $quantity;

            if ($newQty > $product['stock_qty']) {
                $response['message'] = "Cannot add more. Only {$product['stock_qty']} available in stock.";
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'price' => (float)$product['price'],
                    'unit' => $product['unit'],
                    'image_url' => $product['image_url'],
                    'quantity' => $newQty,
                    'stock_qty' => (int)$product['stock_qty']
                ];

                $response['success'] = true;
                $response['message'] = "Added {$product['name']} to cart!";
                $response['cart_count'] = get_cart_count();
                $response['cart_subtotal'] = get_cart_subtotal();
                $response['item'] = $_SESSION['cart'][$productId];
            }
        }
    }
} elseif ($action === 'update' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
            $response['message'] = 'Item removed from cart.';
        } else {
            $maxStock = $_SESSION['cart'][$productId]['stock_qty'];
            if ($quantity > $maxStock) {
                $quantity = $maxStock;
                $response['message'] = "Quantity capped to maximum available stock ($maxStock).";
            } else {
                $response['message'] = 'Cart updated.';
            }
            $_SESSION['cart'][$productId]['quantity'] = $quantity;
        }
        $response['success'] = true;
        $response['cart_count'] = get_cart_count();
        $response['cart_subtotal'] = get_cart_subtotal();
    }
} elseif ($action === 'remove' && $productId > 0) {
    if (isset($_SESSION['cart'][$productId])) {
        $name = $_SESSION['cart'][$productId]['name'];
        unset($_SESSION['cart'][$productId]);
        $response['success'] = true;
        $response['message'] = "Removed $name from your cart.";
        $response['cart_count'] = get_cart_count();
        $response['cart_subtotal'] = get_cart_subtotal();
    }
} elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    $response['success'] = true;
    $response['message'] = "Cart has been cleared.";
    $response['cart_count'] = 0;
    $response['cart_subtotal'] = 0.00;
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // If regular form submission, redirect back
    if ($response['success']) {
        set_flash('success', $response['message']);
    } else {
        set_flash('error', $response['message']);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? 'cart.php';
    header("Location: $referer");
    exit;
}
