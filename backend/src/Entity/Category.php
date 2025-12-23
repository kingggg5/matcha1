<?php
declare(strict_types=1);

namespace App\Entity;

class Category
{
    public ?string $id;
    public string $name;
    public string $slug;
    public string $description;
    public string $image;
    public string $createdAt;

    public function __construct(
        string $name,
        string $slug,
        string $description = '',
        string $image = '',
        ?string $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->image = $image;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'createdAt' => $this->createdAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $category = new self(
            $doc['name'],
            $doc['slug'],
            $doc['description'] ?? '',
            $doc['image'] ?? '',
            (string) $doc['_id']
        );
        $category->createdAt = $doc['createdAt'] ?? date('Y-m-d H:i:s');
        return $category;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'createdAt' => $this->createdAt
        ];
    }
}
