<?php
return [
    'app' => [
        'name' => 'Matcha Shop',
        'env' => 'development',
        'debug' => true,
        'url' => 'http://localhost:8000',
        'frontend_url' => 'http://localhost:5173'
    ],
    'database' => [
        'mongodb' => [
            'uri' => 'mongodb+srv://admin:110341@cluster0.qvmct.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0',
            'database' => 'matcha_shop'
        ]
    ],
    'jwt' => [
        'secret' => 'matcha_shop_secret_key_change_in_production_2024',
        'expiry' => 86400 * 7 // 7 days
    ],
    'cors' => [
        'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
    ]
];
