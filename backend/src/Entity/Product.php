<?php
declare(strict_types=1);

namespace App\Entity;

class Product
{
    public ?string $id;
    public string $name;
    public string $description;
    public float $price;
    public float $priceMax;
    public string $image;
    public string $categoryId;
    public array $variants;
    public bool $inStock;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        string $name,
        string $description,
        float $price,
        string $categoryId,
        string $image = '',
        array $variants = [],
        bool $inStock = true,
        float $priceMax = 0,
        ?string $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->priceMax = $priceMax > 0 ? $priceMax : $price;
        $this->image = $image;
        $this->categoryId = $categoryId;
        $this->variants = $variants;
        $this->inStock = $inStock;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'priceMax' => $this->priceMax,
            'image' => $this->image,
            'categoryId' => $this->categoryId,
            'variants' => $this->variants,
            'inStock' => $this->inStock,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        // Handle variants that might be stdClass or array
        $variants = $doc['variants'] ?? [];
        if (is_object($variants)) {
            $variants = (array) $variants;
        }
        if (!is_array($variants)) {
            $variants = [];
        }
        
        $product = new self(
            $doc['name'],
            $doc['description'] ?? '',
            (float) ($doc['price'] ?? 0),
            $doc['categoryId'] ?? '',
            $doc['image'] ?? '',
            array_values($variants),
            $doc['inStock'] ?? true,
            (float) ($doc['priceMax'] ?? $doc['price'] ?? 0),
            (string) $doc['_id']
        );
        $product->createdAt = $doc['createdAt'] ?? date('Y-m-d H:i:s');
        $product->updatedAt = $doc['updatedAt'] ?? date('Y-m-d H:i:s');
        return $product;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'priceMax' => $this->priceMax,
            'image' => $this->image,
            'categoryId' => $this->categoryId,
            'variants' => $this->variants,
            'inStock' => $this->inStock,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
