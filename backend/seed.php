<?php
/**
 * Seed script to populate database with sample data
 * Run: php seed.php
 */

// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    
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

// Load config
$config = require __DIR__ . '/config/config.php';

use App\Core\Database;

// Initialize database
Database::init($config['database']['mongodb']['uri'], $config['database']['mongodb']['database']);

echo "🌱 Seeding database...\n\n";

// Create admin user
$existingAdmin = Database::findOne('users', ['email' => 'admin@matchazuki.com']);

if (!$existingAdmin) {
    Database::insertOne('users', [
        'name' => 'Admin',
        'email' => 'admin@matchazuki.com',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'role' => 'admin',
        'createdAt' => date('Y-m-d H:i:s')
    ]);
    echo "✅ Created admin user: admin@matchazuki.com / admin123\n";
} else {
    echo "ℹ️  Admin user already exists\n";
}

// Create categories
$categoryData = [
    ['name' => 'มัทฉะเกรดพรีเมียม', 'slug' => 'premium', 'description' => 'มัทฉะคุณภาพสูงสุด เหมาะสำหรับชงดื่ม'],
    ['name' => 'มัทฉะเกรดคลาสสิก', 'slug' => 'classic', 'description' => 'มัทฉะคุณภาพดี เหมาะสำหรับทำขนมและเครื่องดื่ม'],
    ['name' => 'มัทฉะผง', 'slug' => 'powder', 'description' => 'มัทฉะผงสำหรับใช้ในอุตสาหกรรม'],
];

$categoryIds = [];
foreach ($categoryData as $cat) {
    $existing = Database::findOne('categories', ['slug' => $cat['slug']]);
    if (!$existing) {
        $id = Database::insertOne('categories', [
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'description' => $cat['description'],
            'image' => '',
            'createdAt' => date('Y-m-d H:i:s')
        ]);
        $categoryIds[$cat['slug']] = $id;
        echo "✅ Created category: {$cat['name']}\n";
    } else {
        $categoryIds[$cat['slug']] = (string) $existing['_id'];
        echo "ℹ️  Category exists: {$cat['name']}\n";
    }
}

// Create products
$productData = [
    [
        'name' => 'MATCHAZUKI EXCELLENT',
        'description' => 'มัทฉะเกรดพรีเมียมคุณภาพสูงสุด นำเข้าจากเมืองอุจิ ประเทศญี่ปุ่น เหมาะสำหรับชงดื่มโดยตรง รสชาติกลมกล่อม หอมละมุน',
        'price' => 525,
        'priceMax' => 4790,
        'categoryId' => $categoryIds['premium'] ?? '',
        'variants' => ['Classic 40g', 'Excellent 100g', 'Excellent 500g'],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'MATCHAZUKI CLASSIC',
        'description' => 'มัทฉะเกรดคลาสสิก คุณภาพดีเยี่ยม เหมาะสำหรับทำเครื่องดื่มมัทฉะลาเต้ ขนมหวาน และเบเกอรี่',
        'price' => 350,
        'priceMax' => 2500,
        'categoryId' => $categoryIds['classic'] ?? '',
        'variants' => ['Classic 100g', 'Classic 40g', 'Classic 500g'],
        'inStock' => false,
        'image' => 'https://images.unsplash.com/photo-1558160074-4d7d8bdf4256?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'MATCHAZUKI CLASSIC (POWDER) 1KG',
        'description' => 'มัทฉะผงขนาด 1 กิโลกรัม เหมาะสำหรับร้านกาแฟ ร้านเบเกอรี่ หรือผู้ที่ต้องการใช้ปริมาณมาก',
        'price' => 4190,
        'priceMax' => 4190,
        'categoryId' => $categoryIds['powder'] ?? '',
        'variants' => [],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1582793988951-9aed5509eb97?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Organic Ceremonial Matcha',
        'description' => 'มัทฉะออร์แกนิคเกรดพิธีชงชา ผ่านการรับรองมาตรฐาน JAS Organic จากประเทศญี่ปุ่น',
        'price' => 1290,
        'priceMax' => 3890,
        'categoryId' => $categoryIds['premium'] ?? '',
        'variants' => ['30g', '80g', '200g'],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1563822249366-3efb23b8e0c9?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Matcha Latte Mix',
        'description' => 'มัทฉะลาเต้พร้อมชง ผสมนมผงและน้ำตาล เพียงเติมน้ำร้อนก็พร้อมดื่ม',
        'price' => 189,
        'priceMax' => 520,
        'categoryId' => $categoryIds['classic'] ?? '',
        'variants' => ['5 ซอง', '15 ซอง'],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Culinary Grade Matcha 500g',
        'description' => 'มัทฉะเกรดทำขนม เหมาะสำหรับทำเค้ก คุกกี้ ไอศกรีม และขนมหวานต่างๆ',
        'price' => 890,
        'priceMax' => 890,
        'categoryId' => $categoryIds['powder'] ?? '',
        'variants' => [],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400&h=400&fit=crop'
    ]
];

foreach ($productData as $prod) {
    $existing = Database::findOne('products', ['name' => $prod['name']]);
    if (!$existing) {
        Database::insertOne('products', [
            'name' => $prod['name'],
            'description' => $prod['description'],
            'price' => $prod['price'],
            'priceMax' => $prod['priceMax'],
            'categoryId' => $prod['categoryId'],
            'variants' => $prod['variants'],
            'inStock' => $prod['inStock'],
            'image' => $prod['image'],
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
        echo "✅ Created product: {$prod['name']}\n";
    } else {
        echo "ℹ️  Product exists: {$prod['name']}\n";
    }
}

// Create sample coupons
$couponData = [
    [
        'code' => 'MATCHA10',
        'type' => 'percentage',
        'value' => 10,
        'minOrderAmount' => 500,
        'usageLimit' => 100,
        'expiresAt' => date('Y-m-d', strtotime('+30 days')),
        'active' => true
    ],
    [
        'code' => 'NEWUSER50',
        'type' => 'fixed',
        'value' => 50,
        'minOrderAmount' => 300,
        'usageLimit' => 50,
        'expiresAt' => date('Y-m-d', strtotime('+14 days')),
        'active' => true
    ],
    [
        'code' => 'FREESHIP',
        'type' => 'fixed',
        'value' => 100,
        'minOrderAmount' => 1000,
        'usageLimit' => 0,
        'expiresAt' => '',
        'active' => true
    ]
];

foreach ($couponData as $coupon) {
    $existing = Database::findOne('coupons', ['code' => $coupon['code']]);
    if (!$existing) {
        Database::insertOne('coupons', [
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'minOrderAmount' => $coupon['minOrderAmount'],
            'usageLimit' => $coupon['usageLimit'],
            'usedCount' => 0,
            'expiresAt' => $coupon['expiresAt'],
            'active' => $coupon['active'],
            'createdAt' => date('Y-m-d H:i:s')
        ]);
        echo "✅ Created coupon: {$coupon['code']}\n";
    } else {
        echo "ℹ️  Coupon exists: {$coupon['code']}\n";
    }
}

echo "\n🎉 Seeding completed!\n";
echo "\n📝 Admin Login:\n";
echo "   Email: admin@matchazuki.com\n";
echo "   Password: admin123\n";
echo "\n🎟️ Sample Coupons:\n";
echo "   MATCHA10 - ลด 10%\n";
echo "   NEWUSER50 - ลด ฿50\n";
echo "   FREESHIP - ลด ฿100\n";

