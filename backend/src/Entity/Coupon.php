<?php
declare(strict_types=1);

namespace App\Entity;

class Coupon
{
    public ?string $id;
    public string $code;
    public string $type; // 'percentage' or 'fixed'
    public float $value;
    public float $minOrderAmount;
    public int $usageLimit;
    public int $usedCount;
    public string $expiresAt;
    public bool $active;
    public string $createdAt;

    public function __construct(
        string $code,
        string $type,
        float $value,
        float $minOrderAmount = 0,
        int $usageLimit = 0,
        string $expiresAt = '',
        bool $active = true,
        ?string $id = null
    ) {
        $this->id = $id;
        $this->code = strtoupper($code);
        $this->type = $type;
        $this->value = $value;
        $this->minOrderAmount = $minOrderAmount;
        $this->usageLimit = $usageLimit;
        $this->usedCount = 0;
        $this->expiresAt = $expiresAt;
        $this->active = $active;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'minOrderAmount' => $this->minOrderAmount,
            'usageLimit' => $this->usageLimit,
            'usedCount' => $this->usedCount,
            'expiresAt' => $this->expiresAt,
            'active' => $this->active,
            'createdAt' => $this->createdAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $coupon = new self(
            $doc['code'],
            $doc['type'],
            (float) $doc['value'],
            (float) ($doc['minOrderAmount'] ?? 0),
            (int) ($doc['usageLimit'] ?? 0),
            $doc['expiresAt'] ?? '',
            $doc['active'] ?? true,
            (string) $doc['_id']
        );
        $coupon->usedCount = (int) ($doc['usedCount'] ?? 0);
        $coupon->createdAt = $doc['createdAt'] ?? date('Y-m-d H:i:s');
        return $coupon;
    }

    public function isValid(float $orderAmount): array
    {
        if (!$this->active) {
            return ['valid' => false, 'error' => 'คูปองนี้ไม่ได้เปิดใช้งาน'];
        }

        if ($this->expiresAt && strtotime($this->expiresAt) < time()) {
            return ['valid' => false, 'error' => 'คูปองหมดอายุแล้ว'];
        }

        if ($this->usageLimit > 0 && $this->usedCount >= $this->usageLimit) {
            return ['valid' => false, 'error' => 'คูปองถูกใช้ครบจำนวนแล้ว'];
        }

        if ($this->minOrderAmount > 0 && $orderAmount < $this->minOrderAmount) {
            return ['valid' => false, 'error' => "ยอดสั่งซื้อขั้นต่ำ ฿{$this->minOrderAmount}"];
        }

        return ['valid' => true, 'error' => null];
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->type === 'percentage') {
            return round($orderAmount * ($this->value / 100), 2);
        }
        return min($this->value, $orderAmount);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'minOrderAmount' => $this->minOrderAmount,
            'usageLimit' => $this->usageLimit,
            'usedCount' => $this->usedCount,
            'expiresAt' => $this->expiresAt,
            'active' => $this->active,
            'createdAt' => $this->createdAt
        ];
    }
}
