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

// Clear existing products first
echo "🗑️  Clearing existing products...\n";
Database::deleteMany('products', []);

// Create admin user
$existingAdmin = Database::findOne('users', ['email' => 'admin@matchaking.com']);

if (!$existingAdmin) {
    Database::insertOne('users', [
        'name' => 'Admin',
        'email' => 'admin@matchaking.com',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'role' => 'admin',
        'createdAt' => date('Y-m-d H:i:s')
    ]);
    echo "✅ Created admin user: admin@matchaking.com / admin123\n";
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

// Create products with proper variant pricing
$productData = [
    [
        'name' => 'MATCHAKING EXCELLENT',
        'description' => 'มัทฉะเกรดพรีเมียมคุณภาพสูงสุด นำเข้าจากเมืองอุจิ ประเทศญี่ปุ่น เหมาะสำหรับชงดื่มโดยตรง รสชาติกลมกล่อม หอมละมุน',
        'price' => 525,
        'categoryId' => $categoryIds['premium'] ?? '',
        'variants' => [
            ['name' => '40g', 'price' => 525],
            ['name' => '100g', 'price' => 1190],
            ['name' => '500g', 'price' => 4790]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'MATCHAKING CLASSIC',
        'description' => 'มัทฉะเกรดคลาสสิก คุณภาพดีเยี่ยม เหมาะสำหรับทำเครื่องดื่มมัทฉะลาเต้ ขนมหวาน และเบเกอรี่',
        'price' => 350,
        'categoryId' => $categoryIds['classic'] ?? '',
        'variants' => [
            ['name' => '40g', 'price' => 350],
            ['name' => '100g', 'price' => 790],
            ['name' => '500g', 'price' => 2500]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1558160074-4d7d8bdf4256?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'MATCHAKING CLASSIC (POWDER) 1KG',
        'description' => 'มัทฉะผงขนาด 1 กิโลกรัม เหมาะสำหรับร้านกาแฟ ร้านเบเกอรี่ หรือผู้ที่ต้องการใช้ปริมาณมาก',
        'price' => 4190,
        'categoryId' => $categoryIds['powder'] ?? '',
        'variants' => [],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1582793988951-9aed5509eb97?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Organic Ceremonial Matcha',
        'description' => 'มัทฉะออร์แกนิคเกรดพิธีชงชา ผ่านการรับรองมาตรฐาน JAS Organic จากประเทศญี่ปุ่น',
        'price' => 890,
        'categoryId' => $categoryIds['premium'] ?? '',
        'variants' => [
            ['name' => '30g', 'price' => 890],
            ['name' => '80g', 'price' => 1890],
            ['name' => '200g', 'price' => 3890]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1563822249366-3efb23b8e0c9?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Matcha Latte Mix',
        'description' => 'มัทฉะลาเต้พร้อมชง ผสมนมผงและน้ำตาล เพียงเติมน้ำร้อนก็พร้อมดื่ม',
        'price' => 189,
        'categoryId' => $categoryIds['classic'] ?? '',
        'variants' => [
            ['name' => '5 ซอง', 'price' => 189],
            ['name' => '15 ซอง', 'price' => 520]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Culinary Grade Matcha 500g',
        'description' => 'มัทฉะเกรดทำขนม เหมาะสำหรับทำเค้ก คุกกี้ ไอศกรีม และขนมหวานต่างๆ',
        'price' => 890,
        'categoryId' => $categoryIds['powder'] ?? '',
        'variants' => [],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Matcha Starter Kit',
        'description' => 'ชุดเริ่มต้นมัทฉะ พร้อมผงมัทฉะ ช้อนตักไม้ไผ่ และแก้วชงชา',
        'price' => 1590,
        'categoryId' => $categoryIds['premium'] ?? '',
        'variants' => [
            ['name' => 'Classic Set', 'price' => 1590],
            ['name' => 'Premium Set', 'price' => 2490]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1596464716127-f2a82984de30?w=400&h=400&fit=crop'
    ],
    [
        'name' => 'Matcha Cold Brew Bottle',
        'description' => 'มัทฉะสำหรับชงเย็น พร้อมขวดแก้วพิเศษสำหรับชงชาเย็น',
        'price' => 690,
        'categoryId' => $categoryIds['classic'] ?? '',
        'variants' => [
            ['name' => 'ขวดเดี่ยว', 'price' => 690],
            ['name' => 'แพ็ค 3 ขวด', 'price' => 1890]
        ],
        'inStock' => true,
        'image' => 'https://images.unsplash.com/photo-1556679343-c1917e48a5a6?w=400&h=400&fit=crop'
    ]
];

foreach ($productData as $prod) {
    // Calculate priceMax from variants
    $prices = array_map(function ($v) use ($prod) {
        return isset($v['price']) ? $v['price'] : $prod['price'];
    }, $prod['variants']);

    $priceMax = count($prices) > 0 ? max($prices) : $prod['price'];

    Database::insertOne('products', [
        'name' => $prod['name'],
        'description' => $prod['description'],
        'price' => $prod['price'],
        'priceMax' => $priceMax,
        'categoryId' => $prod['categoryId'],
        'variants' => $prod['variants'],
        'inStock' => $prod['inStock'],
        'image' => $prod['image'],
        'createdAt' => date('Y-m-d H:i:s'),
        'updatedAt' => date('Y-m-d H:i:s')
    ]);
    echo "✅ Created product: {$prod['name']}\n";
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
echo "   Email: admin@matchaking.com\n";
echo "   Password: admin123\n";
echo "\n🎟️ Sample Coupons:\n";
echo "   MATCHA10 - ลด 10%\n";
echo "   NEWUSER50 - ลด ฿50\n";
echo "   FREESHIP - ลด ฿100\n";
