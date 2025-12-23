<?php
declare(strict_types=1);

namespace App\Entity;

class User
{
    public ?string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $role;
    public string $createdAt;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role = 'user',
        ?string $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'createdAt' => $this->createdAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $user = new self(
            $doc['name'],
            $doc['email'],
            $doc['password'],
            $doc['role'] ?? 'user',
            (string) $doc['_id']
        );
        $user->createdAt = $doc['createdAt'] ?? date('Y-m-d H:i:s');
        return $user;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'createdAt' => $this->createdAt
        ];
    }
}
