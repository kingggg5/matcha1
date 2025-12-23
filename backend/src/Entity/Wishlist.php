<?php
declare(strict_types=1);

namespace App\Entity;

class Wishlist
{
    public ?string $id;
    public string $userId;
    public array $productIds;
    public string $updatedAt;

    public function __construct(
        string $userId,
        array $productIds = [],
        ?string $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->productIds = $productIds;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'productIds' => $this->productIds,
            'updatedAt' => $this->updatedAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $productIds = $doc['productIds'] ?? [];
        if (is_object($productIds)) {
            $productIds = (array) $productIds;
        }
        
        $wishlist = new self(
            $doc['userId'],
            array_values($productIds),
            (string) $doc['_id']
        );
        $wishlist->updatedAt = $doc['updatedAt'] ?? date('Y-m-d H:i:s');
        return $wishlist;
    }

    public function addProduct(string $productId): bool
    {
        if (in_array($productId, $this->productIds)) {
            return false;
        }
        $this->productIds[] = $productId;
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }

    public function removeProduct(string $productId): bool
    {
        $key = array_search($productId, $this->productIds);
        if ($key === false) {
            return false;
        }
        unset($this->productIds[$key]);
        $this->productIds = array_values($this->productIds);
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }

    public function hasProduct(string $productId): bool
    {
        return in_array($productId, $this->productIds);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'productIds' => $this->productIds,
            'updatedAt' => $this->updatedAt
        ];
    }
}
