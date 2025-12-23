<?php
declare(strict_types=1);

namespace App\Entity;

class Cart
{
    public ?string $id;
    public string $userId;
    public array $items;
    public string $updatedAt;

    public function __construct(
        string $userId,
        array $items = [],
        ?string $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->items = $items;
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'items' => $this->items,
            'updatedAt' => $this->updatedAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $items = $doc['items'] ?? [];
        if (is_object($items)) {
            $items = (array) $items;
        }
        // Convert stdClass items to arrays
        $items = array_map(function($item) {
            return is_object($item) ? (array) $item : $item;
        }, $items);
        
        $cart = new self(
            $doc['userId'],
            array_values($items),
            (string) $doc['_id']
        );
        $cart->updatedAt = $doc['updatedAt'] ?? date('Y-m-d H:i:s');
        return $cart;
    }

    public function addItem(string $productId, int $quantity, string $variant = ''): void
    {
        foreach ($this->items as &$item) {
            if ($item['productId'] === $productId && ($item['variant'] ?? '') === $variant) {
                $item['quantity'] += $quantity;
                $this->updatedAt = date('Y-m-d H:i:s');
                return;
            }
        }
        
        $this->items[] = [
            'id' => bin2hex(random_bytes(8)),
            'productId' => $productId,
            'quantity' => $quantity,
            'variant' => $variant
        ];
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function updateItem(string $itemId, int $quantity): bool
    {
        foreach ($this->items as &$item) {
            if ($item['id'] === $itemId) {
                $item['quantity'] = $quantity;
                $this->updatedAt = date('Y-m-d H:i:s');
                return true;
            }
        }
        return false;
    }

    public function removeItem(string $itemId): bool
    {
        foreach ($this->items as $key => $item) {
            if ($item['id'] === $itemId) {
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                $this->updatedAt = date('Y-m-d H:i:s');
                return true;
            }
        }
        return false;
    }

    public function clear(): void
    {
        $this->items = [];
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'items' => $this->items,
            'updatedAt' => $this->updatedAt
        ];
    }
}
