<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Category;

class CategoryRepository
{
    public function findAll(): array
    {
        $docs = Database::find('categories', [], ['sort' => ['name' => 1]]);
        $categories = [];
        
        foreach ($docs as $doc) {
            $categories[] = Category::fromDocument($doc);
        }
        
        return $categories;
    }

    public function findById(string $id): ?Category
    {
        try {
            $doc = Database::findOne('categories', ['_id' => Database::objectId($id)]);
            return $doc ? Category::fromDocument($doc) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function findBySlug(string $slug): ?Category
    {
        $doc = Database::findOne('categories', ['slug' => $slug]);
        return $doc ? Category::fromDocument($doc) : null;
    }

    public function create(Category $category): string
    {
        $data = $category->toArray();
        unset($data['_id']);
        return Database::insertOne('categories', $data);
    }

    public function update(Category $category): bool
    {
        $data = $category->toArray();
        unset($data['_id']);
        
        Database::updateOne('categories', ['_id' => Database::objectId($category->id)], $data);
        return true;
    }

    public function delete(string $id): bool
    {
        $deleted = Database::deleteOne('categories', ['_id' => Database::objectId($id)]);
        return $deleted > 0;
    }
}
