<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;

class CategoryController
{
    private array $config;
    private CategoryRepository $categoryRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->categoryRepo = new CategoryRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $categories = $this->categoryRepo->findAll();
        
        return [
            'data' => [
                'categories' => array_map(fn($c) => $c->toPublicArray(), $categories)
            ],
            'status' => 200
        ];
    }

    public function show(array $params, array $body, ?string $userId): array
    {
        $category = $this->categoryRepo->findById($params['id']);
        
        if (!$category) {
            return ['data' => ['error' => 'Category not found'], 'status' => 404];
        }

        return [
            'data' => ['category' => $category->toPublicArray()],
            'status' => 200
        ];
    }

    public function store(array $params, array $body, ?string $userId): array
    {
        // Validate input
        if (empty($body['name'])) {
            return ['data' => ['error' => 'Name is required'], 'status' => 400];
        }

        // Generate slug if not provided
        $slug = $body['slug'] ?? $this->generateSlug($body['name']);

        // Check if slug exists
        if ($this->categoryRepo->findBySlug($slug)) {
            return ['data' => ['error' => 'Category slug already exists'], 'status' => 409];
        }

        $category = new Category(
            $body['name'],
            $slug,
            $body['description'] ?? '',
            $body['image'] ?? ''
        );

        $id = $this->categoryRepo->create($category);
        $category->id = $id;

        return [
            'data' => [
                'message' => 'Category created successfully',
                'category' => $category->toPublicArray()
            ],
            'status' => 201
        ];
    }

    public function update(array $params, array $body, ?string $userId): array
    {
        $category = $this->categoryRepo->findById($params['id']);
        
        if (!$category) {
            return ['data' => ['error' => 'Category not found'], 'status' => 404];
        }

        // Update fields
        if (isset($body['name'])) $category->name = $body['name'];
        if (isset($body['slug'])) {
            // Check if new slug exists (not same category)
            $existing = $this->categoryRepo->findBySlug($body['slug']);
            if ($existing && $existing->id !== $category->id) {
                return ['data' => ['error' => 'Category slug already exists'], 'status' => 409];
            }
            $category->slug = $body['slug'];
        }
        if (isset($body['description'])) $category->description = $body['description'];
        if (isset($body['image'])) $category->image = $body['image'];

        $this->categoryRepo->update($category);

        return [
            'data' => [
                'message' => 'Category updated successfully',
                'category' => $category->toPublicArray()
            ],
            'status' => 200
        ];
    }

    public function destroy(array $params, array $body, ?string $userId): array
    {
        $category = $this->categoryRepo->findById($params['id']);
        
        if (!$category) {
            return ['data' => ['error' => 'Category not found'], 'status' => 404];
        }

        $this->categoryRepo->delete($params['id']);

        return [
            'data' => ['message' => 'Category deleted successfully'],
            'status' => 200
        ];
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return $slug;
    }
}
