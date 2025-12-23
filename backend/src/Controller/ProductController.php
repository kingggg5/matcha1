<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;

class ProductController
{
    private array $config;
    private ProductRepository $productRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->productRepo = new ProductRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $filters = [];
        
        if (!empty($_GET['categoryId'])) {
            $filters['categoryId'] = $_GET['categoryId'];
        }
        
        if (isset($_GET['inStock'])) {
            $filters['inStock'] = $_GET['inStock'] === 'true';
        }
        
        if (!empty($_GET['limit'])) {
            $filters['limit'] = (int) $_GET['limit'];
        }

        // Search filter
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        // Price range filters
        if (!empty($_GET['minPrice'])) {
            $filters['minPrice'] = (float) $_GET['minPrice'];
        }
        if (!empty($_GET['maxPrice'])) {
            $filters['maxPrice'] = (float) $_GET['maxPrice'];
        }

        // Sort filter (price_asc, price_desc)
        if (!empty($_GET['sort'])) {
            $filters['sort'] = $_GET['sort'];
        }

        $products = $this->productRepo->findAll($filters);
        
        return [
            'data' => [
                'products' => array_map(fn($p) => $p->toPublicArray(), $products)
            ],
            'status' => 200
        ];
    }

    public function show(array $params, array $body, ?string $userId): array
    {
        $product = $this->productRepo->findById($params['id']);
        
        if (!$product) {
            return ['data' => ['error' => 'Product not found'], 'status' => 404];
        }

        return [
            'data' => ['product' => $product->toPublicArray()],
            'status' => 200
        ];
    }

    public function store(array $params, array $body, ?string $userId): array
    {
        // Validate input
        if (empty($body['name']) || !isset($body['price'])) {
            return ['data' => ['error' => 'Name and price are required'], 'status' => 400];
        }

        $product = new Product(
            $body['name'],
            $body['description'] ?? '',
            (float) $body['price'],
            $body['categoryId'] ?? '',
            $body['image'] ?? '',
            $body['variants'] ?? [],
            $body['inStock'] ?? true,
            (float) ($body['priceMax'] ?? $body['price'])
        );

        $id = $this->productRepo->create($product);
        $product->id = $id;

        return [
            'data' => [
                'message' => 'Product created successfully',
                'product' => $product->toPublicArray()
            ],
            'status' => 201
        ];
    }

    public function update(array $params, array $body, ?string $userId): array
    {
        $product = $this->productRepo->findById($params['id']);
        
        if (!$product) {
            return ['data' => ['error' => 'Product not found'], 'status' => 404];
        }

        // Update fields
        if (isset($body['name'])) $product->name = $body['name'];
        if (isset($body['description'])) $product->description = $body['description'];
        if (isset($body['price'])) $product->price = (float) $body['price'];
        if (isset($body['priceMax'])) $product->priceMax = (float) $body['priceMax'];
        if (isset($body['image'])) $product->image = $body['image'];
        if (isset($body['categoryId'])) $product->categoryId = $body['categoryId'];
        if (isset($body['variants'])) $product->variants = $body['variants'];
        if (isset($body['inStock'])) $product->inStock = (bool) $body['inStock'];

        $this->productRepo->update($product);

        return [
            'data' => [
                'message' => 'Product updated successfully',
                'product' => $product->toPublicArray()
            ],
            'status' => 200
        ];
    }

    public function destroy(array $params, array $body, ?string $userId): array
    {
        $product = $this->productRepo->findById($params['id']);
        
        if (!$product) {
            return ['data' => ['error' => 'Product not found'], 'status' => 404];
        }

        $this->productRepo->delete($params['id']);

        return [
            'data' => ['message' => 'Product deleted successfully'],
            'status' => 200
        ];
    }

    public function uploadImage(array $params, array $body, ?string $userId): array
    {
        $product = $this->productRepo->findById($params['id']);
        
        if (!$product) {
            return ['data' => ['error' => 'Product not found'], 'status' => 404];
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return ['data' => ['error' => 'No image uploaded'], 'status' => 400];
        }

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['data' => ['error' => 'Invalid image type'], 'status' => 400];
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('product_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['data' => ['error' => 'Failed to upload image'], 'status' => 500];
        }

        $product->image = '/uploads/' . $filename;
        $this->productRepo->update($product);

        return [
            'data' => [
                'message' => 'Image uploaded successfully',
                'image' => $product->image
            ],
            'status' => 200
        ];
    }
}
