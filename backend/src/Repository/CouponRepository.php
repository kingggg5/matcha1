<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Coupon;

class CouponRepository
{
    public function findAll(): array
    {
        $docs = Database::find('coupons', [], ['sort' => ['createdAt' => -1]]);
        $coupons = [];
        foreach ($docs as $doc) {
            $coupons[] = Coupon::fromDocument($doc);
        }
        return $coupons;
    }

    public function findById(string $id): ?Coupon
    {
        $doc = Database::findOne('coupons', ['_id' => Database::objectId($id)]);
        return $doc ? Coupon::fromDocument($doc) : null;
    }

    public function findByCode(string $code): ?Coupon
    {
        $doc = Database::findOne('coupons', ['code' => strtoupper($code)]);
        return $doc ? Coupon::fromDocument($doc) : null;
    }

    public function create(Coupon $coupon): string
    {
        $data = $coupon->toArray();
        unset($data['_id']);
        return Database::insertOne('coupons', $data);
    }

    public function update(Coupon $coupon): bool
    {
        $data = $coupon->toArray();
        unset($data['_id']);
        Database::updateOne('coupons', ['_id' => Database::objectId($coupon->id)], $data);
        return true;
    }

    public function delete(string $id): bool
    {
        return Database::deleteOne('coupons', ['_id' => Database::objectId($id)]) > 0;
    }

    public function incrementUsage(string $id): void
    {
        $coupon = $this->findById($id);
        if ($coupon) {
            $coupon->usedCount++;
            $this->update($coupon);
        }
    }
}
