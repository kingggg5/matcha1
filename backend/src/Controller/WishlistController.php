<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\WishlistRepository;
use App\Repository\ProductRepository;

class WishlistController
{
    private array $config;
    private WishlistRepository $wishlistRepo;
    private ProductRepository $productRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->wishlistRepo = new WishlistRepository();
        $this->productRepo = new ProductRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $wishlist = $this->wishlistRepo->getOrCreate($userId);
        
        // Enrich with product data
        $products = [];
        foreach ($wishlist->productIds as $productId) {
            $product = $this->productRepo->findById($productId);
            if ($product) {
                $products[] = $product->toPublicArray();
            }
        }

        return [
            'data' => [
                'wishlist' => $wishlist->toPublicArray(),
                'products' => $products
            ],
            'status' => 200
        ];
    }

    public function add(array $params, array $body, ?string $userId): array
    {
        $productId = $body['productId'] ?? '';
        if (empty($productId)) {
            return ['data' => ['error' => 'กรุณาระบุสินค้า'], 'status' => 400];
        }

        $wishlist = $this->wishlistRepo->getOrCreate($userId);
        $wishlist->addProduct($productId);
        $this->wishlistRepo->save($wishlist);

        return [
            'data' => ['wishlist' => $wishlist->toPublicArray()],
            'status' => 200
        ];
    }

    public function remove(array $params, array $body, ?string $userId): array
    {
        $wishlist = $this->wishlistRepo->getOrCreate($userId);
        $wishlist->removeProduct($params['id']);
        $this->wishlistRepo->save($wishlist);

        return [
            'data' => ['wishlist' => $wishlist->toPublicArray()],
            'status' => 200
        ];
    }

    public function check(array $params, array $body, ?string $userId): array
    {
        $wishlist = $this->wishlistRepo->getOrCreate($userId);
        $inWishlist = $wishlist->hasProduct($params['id']);

        return [
            'data' => ['inWishlist' => $inWishlist],
            'status' => 200
        ];
    }
}
