<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Wishlist;

class WishlistRepository
{
    public function findByUserId(string $userId): ?Wishlist
    {
        $doc = Database::findOne('wishlists', ['userId' => $userId]);
        return $doc ? Wishlist::fromDocument($doc) : null;
    }

    public function create(Wishlist $wishlist): string
    {
        $data = $wishlist->toArray();
        unset($data['_id']);
        return Database::insertOne('wishlists', $data);
    }

    public function update(Wishlist $wishlist): bool
    {
        $data = $wishlist->toArray();
        unset($data['_id']);
        Database::updateOne('wishlists', ['_id' => Database::objectId($wishlist->id)], $data);
        return true;
    }

    public function getOrCreate(string $userId): Wishlist
    {
        $wishlist = $this->findByUserId($userId);
        if (!$wishlist) {
            $wishlist = new Wishlist($userId);
            $id = $this->create($wishlist);
            $wishlist->id = $id;
        }
        return $wishlist;
    }

    public function save(Wishlist $wishlist): void
    {
        if ($wishlist->id) {
            $this->update($wishlist);
        } else {
            $wishlist->id = $this->create($wishlist);
        }
    }
}
