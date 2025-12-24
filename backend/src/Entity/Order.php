<?php
declare(strict_types=1);

namespace App\Entity;

class Order
{
    public ?string $id;
    public string $userId;
    public array $items;
    public float $subtotal;
    public float $discount;
    public ?string $couponCode;
    public float $total;
    public string $status; // pending, paid, confirmed, shipped, completed, cancelled
    public string $paymentSlip;
    public string $shippingAddress;
    public string $customerName;
    public string $customerPhone;
    public string $customerEmail;
    public string $note;
    public string $createdAt;
    public string $updatedAt;
    public string $paidAt;

    public function __construct(
        string $userId,
        array $items,
        float $subtotal,
        float $total,
        float $discount = 0,
        ?string $couponCode = null,
        ?string $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->items = $items;
        $this->subtotal = $subtotal;
        $this->discount = $discount;
        $this->couponCode = $couponCode;
        $this->total = $total;
        $this->status = 'pending';
        $this->paymentSlip = '';
        $this->shippingAddress = '';
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->note = '';
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        $this->paidAt = '';
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'couponCode' => $this->couponCode,
            'total' => $this->total,
            'status' => $this->status,
            'paymentSlip' => $this->paymentSlip,
            'shippingAddress' => $this->shippingAddress,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'customerEmail' => $this->customerEmail,
            'note' => $this->note,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'paidAt' => $this->paidAt
        ];
    }

    public static function fromDocument(array $doc): self
    {
        $items = $doc['items'] ?? [];
        if (is_object($items)) {
            $items = (array) $items;
        }
        $items = array_map(fn($i) => is_object($i) ? (array) $i : $i, $items);

        $order = new self(
            $doc['userId'],
            array_values($items),
            (float) $doc['subtotal'],
            (float) $doc['total'],
            (float) ($doc['discount'] ?? 0),
            $doc['couponCode'] ?? null,
            (string) $doc['_id']
        );
        $order->status = $doc['status'] ?? 'pending';
        $order->paymentSlip = $doc['paymentSlip'] ?? '';
        $order->shippingAddress = $doc['shippingAddress'] ?? '';
        $order->customerName = $doc['customerName'] ?? '';
        $order->customerPhone = $doc['customerPhone'] ?? '';
        $order->customerEmail = $doc['customerEmail'] ?? '';
        $order->note = $doc['note'] ?? '';
        $order->createdAt = self::convertDate($doc['createdAt'] ?? null);
        $order->updatedAt = self::convertDate($doc['updatedAt'] ?? null);
        $order->paidAt = self::convertDate($doc['paidAt'] ?? null, true);
        return $order;
    }

    private static function convertDate($date, bool $allowEmpty = false): string
    {
        if ($date === null || $date === '') {
            return $allowEmpty ? '' : date('Y-m-d H:i:s');
        }
        if ($date instanceof \MongoDB\BSON\UTCDateTime) {
            return $date->toDateTime()->format('Y-m-d H:i:s');
        }
        if (is_string($date)) {
            return $date;
        }
        return $allowEmpty ? '' : date('Y-m-d H:i:s');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'couponCode' => $this->couponCode,
            'total' => $this->total,
            'status' => $this->status,
            'paymentSlip' => $this->paymentSlip,
            'shippingAddress' => $this->shippingAddress,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'customerEmail' => $this->customerEmail,
            'note' => $this->note,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'paidAt' => $this->paidAt
        ];
    }
}
