<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\ProductRepository;

class CartController
{
    private array $config;
    private CartRepository $cartRepo;
    private ProductRepository $productRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->cartRepo = new CartRepository();
        $this->productRepo = new ProductRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $cart = $this->cartRepo->getOrCreate($userId);
        
        // Enrich cart items with product details
        $enrichedItems = [];
        $productIds = array_column($cart->items, 'productId');
        
        if (!empty($productIds)) {
            $products = $this->productRepo->findByIds($productIds);
            
            foreach ($cart->items as $item) {
                $product = $products[$item['productId']] ?? null;
                $enrichedItems[] = [
                    'id' => $item['id'],
                    'productId' => $item['productId'],
                    'quantity' => $item['quantity'],
                    'variant' => $item['variant'],
                    'product' => $product ? $product->toPublicArray() : null
                ];
            }
        }

        return [
            'data' => [
                'cart' => [
                    'id' => $cart->id,
                    'items' => $enrichedItems,
                    'itemCount' => array_sum(array_column($cart->items, 'quantity')),
                    'updatedAt' => $cart->updatedAt
                ]
            ],
            'status' => 200
        ];
    }

    public function addItem(array $params, array $body, ?string $userId): array
    {
        // Validate input
        if (empty($body['productId'])) {
            return ['data' => ['error' => 'Product ID is required'], 'status' => 400];
        }

        // Verify product exists
        $product = $this->productRepo->findById($body['productId']);
        if (!$product) {
            return ['data' => ['error' => 'Product not found'], 'status' => 404];
        }

        $cart = $this->cartRepo->getOrCreate($userId);
        $cart->addItem(
            $body['productId'],
            (int) ($body['quantity'] ?? 1),
            $body['variant'] ?? ''
        );
        $this->cartRepo->save($cart);

        return [
            'data' => [
                'message' => 'Item added to cart',
                'itemCount' => array_sum(array_column($cart->items, 'quantity'))
            ],
            'status' => 200
        ];
    }

    public function updateItem(array $params, array $body, ?string $userId): array
    {
        if (!isset($body['quantity'])) {
            return ['data' => ['error' => 'Quantity is required'], 'status' => 400];
        }

        $cart = $this->cartRepo->getOrCreate($userId);
        
        if (!$cart->updateItem($params['id'], (int) $body['quantity'])) {
            return ['data' => ['error' => 'Item not found in cart'], 'status' => 404];
        }

        $this->cartRepo->save($cart);

        return [
            'data' => [
                'message' => 'Cart item updated',
                'itemCount' => array_sum(array_column($cart->items, 'quantity'))
            ],
            'status' => 200
        ];
    }

    public function removeItem(array $params, array $body, ?string $userId): array
    {
        $cart = $this->cartRepo->getOrCreate($userId);
        
        if (!$cart->removeItem($params['id'])) {
            return ['data' => ['error' => 'Item not found in cart'], 'status' => 404];
        }

        $this->cartRepo->save($cart);

        return [
            'data' => [
                'message' => 'Item removed from cart',
                'itemCount' => array_sum(array_column($cart->items, 'quantity'))
            ],
            'status' => 200
        ];
    }

    public function clear(array $params, array $body, ?string $userId): array
    {
        $cart = $this->cartRepo->getOrCreate($userId);
        $cart->clear();
        $this->cartRepo->save($cart);

        return [
            'data' => ['message' => 'Cart cleared'],
            'status' => 200
        ];
    }
}
