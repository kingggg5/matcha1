<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\CouponRepository;
use App\Entity\Coupon;

class CouponController
{
    private array $config;
    private CouponRepository $couponRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->couponRepo = new CouponRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $coupons = $this->couponRepo->findAll();
        $result = array_map(fn($c) => $c->toPublicArray(), $coupons);
        return [
            'data' => ['coupons' => $result],
            'status' => 200
        ];
    }

    public function show(array $params, array $body, ?string $userId): array
    {
        $coupon = $this->couponRepo->findById($params['id']);
        if (!$coupon) {
            return ['data' => ['error' => 'ไม่พบคูปอง'], 'status' => 404];
        }
        return [
            'data' => ['coupon' => $coupon->toPublicArray()],
            'status' => 200
        ];
    }

    public function validate(array $params, array $body, ?string $userId): array
    {
        $code = $body['code'] ?? '';
        $orderAmount = (float) ($body['orderAmount'] ?? 0);

        if (empty($code)) {
            return ['data' => ['error' => 'กรุณาระบุรหัสคูปอง'], 'status' => 400];
        }

        $coupon = $this->couponRepo->findByCode($code);
        if (!$coupon) {
            return ['data' => ['error' => 'ไม่พบคูปองนี้'], 'status' => 404];
        }

        $validation = $coupon->isValid($orderAmount);
        if (!$validation['valid']) {
            return ['data' => ['error' => $validation['error']], 'status' => 400];
        }

        $discount = $coupon->calculateDiscount($orderAmount);
        return [
            'data' => [
                'valid' => true,
                'coupon' => $coupon->toPublicArray(),
                'discount' => $discount
            ],
            'status' => 200
        ];
    }

    public function store(array $params, array $body, ?string $userId): array
    {
        if (empty($body['code']) || empty($body['type']) || !isset($body['value'])) {
            return ['data' => ['error' => 'กรุณากรอกข้อมูลให้ครบ'], 'status' => 400];
        }

        $existing = $this->couponRepo->findByCode($body['code']);
        if ($existing) {
            return ['data' => ['error' => 'รหัสคูปองนี้มีอยู่แล้ว'], 'status' => 400];
        }

        $coupon = new Coupon(
            strtoupper($body['code']),
            $body['type'],
            (float) $body['value'],
            (float) ($body['minOrderAmount'] ?? 0),
            (int) ($body['usageLimit'] ?? 0),
            $body['expiresAt'] ?? '',
            $body['active'] ?? true
        );

        $id = $this->couponRepo->create($coupon);
        $coupon->id = $id;

        return [
            'data' => ['coupon' => $coupon->toPublicArray()],
            'status' => 201
        ];
    }

    public function update(array $params, array $body, ?string $userId): array
    {
        $coupon = $this->couponRepo->findById($params['id']);
        if (!$coupon) {
            return ['data' => ['error' => 'ไม่พบคูปอง'], 'status' => 404];
        }

        $coupon->code = $body['code'] ?? $coupon->code;
        $coupon->type = $body['type'] ?? $coupon->type;
        $coupon->value = (float) ($body['value'] ?? $coupon->value);
        $coupon->minOrderAmount = (float) ($body['minOrderAmount'] ?? $coupon->minOrderAmount);
        $coupon->usageLimit = (int) ($body['usageLimit'] ?? $coupon->usageLimit);
        $coupon->expiresAt = $body['expiresAt'] ?? $coupon->expiresAt;
        $coupon->active = $body['active'] ?? $coupon->active;

        $this->couponRepo->update($coupon);
        
        return [
            'data' => ['coupon' => $coupon->toPublicArray()],
            'status' => 200
        ];
    }

    public function destroy(array $params, array $body, ?string $userId): array
    {
        $coupon = $this->couponRepo->findById($params['id']);
        if (!$coupon) {
            return ['data' => ['error' => 'ไม่พบคูปอง'], 'status' => 404];
        }

        $this->couponRepo->delete($params['id']);
        return [
            'data' => ['message' => 'ลบคูปองสำเร็จ'],
            'status' => 200
        ];
    }
}
