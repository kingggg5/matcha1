<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\User;

class UserRepository
{
    public function findById(string $id): ?User
    {
        $doc = Database::findOne('users', ['_id' => Database::objectId($id)]);
        return $doc ? User::fromDocument($doc) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $doc = Database::findOne('users', ['email' => $email]);
        return $doc ? User::fromDocument($doc) : null;
    }

    public function create(User $user): string
    {
        $data = $user->toArray();
        unset($data['_id']);
        return Database::insertOne('users', $data);
    }

    public function update(User $user): bool
    {
        $data = $user->toArray();
        unset($data['_id']);
        $modified = Database::updateOne('users', ['_id' => Database::objectId($user->id)], $data);
        return $modified > 0;
    }

    public function delete(string $id): bool
    {
        $deleted = Database::deleteOne('users', ['_id' => Database::objectId($id)]);
        return $deleted > 0;
    }
}
