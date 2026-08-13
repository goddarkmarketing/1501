<?php
/**
 * Redirect: promo filters are now managed on promotions.php
 */
require_once __DIR__ . '/includes/auth.php';
header('Location: ' . ADMIN_URL . '/promotions.php#promo-filters-section');
exit;
