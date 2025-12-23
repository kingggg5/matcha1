<?php
declare(strict_types=1);

// Simple autoloader without Composer
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;
use App\Core\Database;

// Load config
$config = require __DIR__ . '/../config/config.php';

// CORS Headers
$allowedOrigins = $config['cors']['allowed_origins'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: " . implode(', ', $config['cors']['allowed_methods']));
header("Access-Control-Allow-Headers: " . implode(', ', $config['cors']['allowed_headers']));
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize database
Database::init($config['database']['mongodb']['uri'], $config['database']['mongodb']['database']);

// Initialize router
$router = new Router($config);

// Auth routes
$router->post('/api/auth/register', 'AuthController@register');
$router->post('/api/auth/login', 'AuthController@login');
$router->get('/api/auth/me', 'AuthController@me', true);

// Product routes
$router->get('/api/products', 'ProductController@index');
$router->get('/api/products/{id}', 'ProductController@show');
$router->post('/api/products', 'ProductController@store', true, true);
$router->put('/api/products/{id}', 'ProductController@update', true, true);
$router->delete('/api/products/{id}', 'ProductController@destroy', true, true);
$router->post('/api/products/{id}/upload', 'ProductController@uploadImage', true, true);

// Category routes
$router->get('/api/categories', 'CategoryController@index');
$router->get('/api/categories/{id}', 'CategoryController@show');
$router->post('/api/categories', 'CategoryController@store', true, true);
$router->put('/api/categories/{id}', 'CategoryController@update', true, true);
$router->delete('/api/categories/{id}', 'CategoryController@destroy', true, true);

// Cart routes
$router->get('/api/cart', 'CartController@index', true);
$router->post('/api/cart/items', 'CartController@addItem', true);
$router->put('/api/cart/items/{id}', 'CartController@updateItem', true);
$router->delete('/api/cart/items/{id}', 'CartController@removeItem', true);
$router->delete('/api/cart', 'CartController@clear', true);

// Coupon routes
$router->get('/api/coupons', 'CouponController@index', true, true);
$router->get('/api/coupons/{id}', 'CouponController@show', true, true);
$router->post('/api/coupons', 'CouponController@store', true, true);
$router->put('/api/coupons/{id}', 'CouponController@update', true, true);
$router->delete('/api/coupons/{id}', 'CouponController@destroy', true, true);
$router->post('/api/coupons/validate', 'CouponController@validate', true);

// Order routes
$router->get('/api/orders', 'OrderController@index', true);
$router->get('/api/orders/{id}', 'OrderController@show', true);
$router->post('/api/orders', 'OrderController@store', true);
$router->post('/api/orders/{id}/slip', 'OrderController@uploadSlip', true);
$router->put('/api/orders/{id}/status', 'OrderController@updateStatus', true, true);
$router->get('/api/admin/stats', 'OrderController@stats', true, true);

// Wishlist routes
$router->get('/api/wishlist', 'WishlistController@index', true);
$router->post('/api/wishlist', 'WishlistController@add', true);
$router->delete('/api/wishlist/{id}', 'WishlistController@remove', true);
$router->get('/api/wishlist/check/{id}', 'WishlistController@check', true);

// Run router
$router->run();

