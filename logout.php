<?php
/**
 * Logout Handler
 * FreshMart Online Grocery Store
 */
require_once __DIR__ . '/config/db.php';

// Unset user session variables
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_address']);

set_flash('info', 'You have been safely logged out.');
header('Location: index.php');
exit;
