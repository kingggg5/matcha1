<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Cart;

class CartRepository
{
    public function findByUserId(string $userId): ?Cart
    {
        $doc = Database::findOne('carts', ['userId' => $userId]);
        return $doc ? Cart::fromDocument($doc) : null;
    }

    public function create(Cart $cart): string
    {
        $data = $cart->toArray();
        unset($data['_id']);
        return Database::insertOne('carts', $data);
    }

    public function update(Cart $cart): bool
    {
        $data = $cart->toArray();
        unset($data['_id']);
        
        Database::updateOne('carts', ['_id' => Database::objectId($cart->id)], $data);
        return true;
    }

    public function getOrCreate(string $userId): Cart
    {
        $cart = $this->findByUserId($userId);
        
        if (!$cart) {
            $cart = new Cart($userId);
            $id = $this->create($cart);
            $cart->id = $id;
        }
        
        return $cart;
    }

    public function save(Cart $cart): void
    {
        if ($cart->id) {
            $this->update($cart);
        } else {
            $cart->id = $this->create($cart);
        }
    }
}
