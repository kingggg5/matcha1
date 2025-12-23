<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Product;

class ProductRepository
{
    public function findAll(array $filters = []): array
    {
        $allDocs = Database::find('products', [], ['sort' => ['createdAt' => -1]]);
        $results = [];

        foreach ($allDocs as $doc) {
            // Category filter
            if (!empty($filters['categoryId']) && ($doc['categoryId'] ?? '') !== $filters['categoryId']) {
                continue;
            }

            // In stock filter
            if (isset($filters['inStock']) && ($doc['inStock'] ?? true) !== $filters['inStock']) {
                continue;
            }

            // Search filter
            if (!empty($filters['search'])) {
                $search = mb_strtolower($filters['search']);
                $name = mb_strtolower($doc['name'] ?? '');
                $desc = mb_strtolower($doc['description'] ?? '');
                if (strpos($name, $search) === false && strpos($desc, $search) === false) {
                    continue;
                }
            }

            // Price range filter
            $price = (float) ($doc['price'] ?? 0);
            if (!empty($filters['minPrice']) && $price < (float) $filters['minPrice']) {
                continue;
            }
            if (!empty($filters['maxPrice']) && $price > (float) $filters['maxPrice']) {
                continue;
            }

            $results[] = $doc;
        }

        // Sort
        if (!empty($filters['sort'])) {
            usort($results, function($a, $b) use ($filters) {
                $priceA = (float) ($a['price'] ?? 0);
                $priceB = (float) ($b['price'] ?? 0);
                if ($filters['sort'] === 'price_asc') {
                    return $priceA <=> $priceB;
                } elseif ($filters['sort'] === 'price_desc') {
                    return $priceB <=> $priceA;
                }
                return 0;
            });
        }

        // Limit
        if (!empty($filters['limit'])) {
            $results = array_slice($results, 0, (int) $filters['limit']);
        }

        // Convert to entities
        $products = [];
        foreach ($results as $doc) {
            $products[] = Product::fromDocument($doc);
        }

        return $products;
    }

    public function findById(string $id): ?Product
    {
        try {
            $doc = Database::findOne('products', ['_id' => Database::objectId($id)]);
            return $doc ? Product::fromDocument($doc) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function create(Product $product): string
    {
        $data = $product->toArray();
        unset($data['_id']);
        return Database::insertOne('products', $data);
    }

    public function update(Product $product): bool
    {
        $data = $product->toArray();
        $data['updatedAt'] = date('Y-m-d H:i:s');
        unset($data['_id']);
        
        Database::updateOne('products', ['_id' => Database::objectId($product->id)], $data);
        return true;
    }

    public function delete(string $id): bool
    {
        $deleted = Database::deleteOne('products', ['_id' => Database::objectId($id)]);
        return $deleted > 0;
    }

    public function findByIds(array $ids): array
    {
        $products = [];
        foreach ($ids as $id) {
            $product = $this->findById($id);
            if ($product) {
                $products[$id] = $product;
            }
        }
        return $products;
    }
}
