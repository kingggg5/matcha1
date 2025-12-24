<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CartRepository;
use App\Repository\CouponRepository;
use App\Repository\UserRepository;
use App\Entity\Order;

class OrderController
{
    private array $config;
    private OrderRepository $orderRepo;
    private ProductRepository $productRepo;
    private CartRepository $cartRepo;
    private CouponRepository $couponRepo;
    private UserRepository $userRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->orderRepo = new OrderRepository();
        $this->productRepo = new ProductRepository();
        $this->cartRepo = new CartRepository();
        $this->couponRepo = new CouponRepository();
        $this->userRepo = new UserRepository();
    }

    public function index(array $params, array $body, ?string $userId): array
    {
        $user = $this->userRepo->findById($userId);
        $role = $user ? $user->role : 'user';

        if ($role === 'admin') {
            $orders = $this->orderRepo->findAll();
        } else {
            $orders = $this->orderRepo->findByUserId($userId);
        }
        
        return [
            'data' => ['orders' => array_map(fn($o) => $o->toPublicArray(), $orders)],
            'status' => 200
        ];
    }

    public function show(array $params, array $body, ?string $userId): array
    {
        $order = $this->orderRepo->findById($params['id']);
        if (!$order) {
            return ['data' => ['error' => 'ไม่พบคำสั่งซื้อ'], 'status' => 404];
        }

        $user = $this->userRepo->findById($userId);
        $role = $user ? $user->role : 'user';

        if ($role !== 'admin' && $order->userId !== $userId) {
            return ['data' => ['error' => 'ไม่มีสิทธิ์ดูคำสั่งซื้อนี้'], 'status' => 403];
        }

        return [
            'data' => ['order' => $order->toPublicArray()],
            'status' => 200
        ];
    }

    public function store(array $params, array $body, ?string $userId): array
    {
        $cart = $this->cartRepo->findByUserId($userId);

        if (!$cart || empty($cart->items)) {
            return ['data' => ['error' => 'ตะกร้าว่าง'], 'status' => 400];
        }

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        foreach ($cart->items as $item) {
            $product = $this->productRepo->findById($item['productId']);
            if ($product) {
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;
                $orderItems[] = [
                    'productId' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'variant' => $item['variant'] ?? '',
                    'total' => $itemTotal
                ];
            }
        }

        // Apply coupon
        $discount = 0;
        $couponCode = $body['couponCode'] ?? null;
        if ($couponCode) {
            $coupon = $this->couponRepo->findByCode($couponCode);
            if ($coupon) {
                $validation = $coupon->isValid($subtotal);
                if ($validation['valid']) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $this->couponRepo->incrementUsage($coupon->id);
                }
            }
        }

        $total = $subtotal - $discount;

        $order = new Order(
            $userId,
            $orderItems,
            $subtotal,
            $total,
            $discount,
            $couponCode
        );
        
        $user = $this->userRepo->findById($userId);
        $order->customerName = $body['customerName'] ?? ($user ? $user->name : '');
        $order->customerEmail = $body['customerEmail'] ?? ($user ? $user->email : '');
        $order->customerPhone = $body['customerPhone'] ?? '';
        $order->shippingAddress = $body['shippingAddress'] ?? '';
        $order->note = $body['note'] ?? '';

        $id = $this->orderRepo->create($order);
        $order->id = $id;

        // Clear cart
        $cart->clear();
        $this->cartRepo->save($cart);

        return [
            'data' => ['order' => $order->toPublicArray()],
            'status' => 201
        ];
    }

    public function uploadSlip(array $params, array $body, ?string $userId): array
    {
        $order = $this->orderRepo->findById($params['id']);
        if (!$order) {
            return ['data' => ['error' => 'ไม่พบคำสั่งซื้อ'], 'status' => 404];
        }

        $user = $this->userRepo->findById($userId);
        $role = $user ? $user->role : 'user';

        if ($order->userId !== $userId && $role !== 'admin') {
            return ['data' => ['error' => 'ไม่มีสิทธิ์'], 'status' => 403];
        }

        if (!isset($_FILES['slip'])) {
            return ['data' => ['error' => 'กรุณาอัพโหลดสลิป'], 'status' => 400];
        }

        $file = $_FILES['slip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            return ['data' => ['error' => 'รองรับไฟล์ JPG, PNG, WEBP เท่านั้น'], 'status' => 400];
        }

        $uploadDir = __DIR__ . '/../../public/uploads/slips/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'slip_' . $params['id'] . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $order->paymentSlip = '/uploads/slips/' . $filename;
            $order->status = 'paid';
            $order->paidAt = date('Y-m-d H:i:s');
            $this->orderRepo->update($order);

            return [
                'data' => [
                    'message' => 'อัพโหลดสลิปสำเร็จ',
                    'order' => $order->toPublicArray()
                ],
                'status' => 200
            ];
        } else {
            return ['data' => ['error' => 'ไม่สามารถอัพโหลดไฟล์ได้'], 'status' => 500];
        }
    }

    public function updateStatus(array $params, array $body, ?string $userId): array
    {
        $order = $this->orderRepo->findById($params['id']);
        if (!$order) {
            return ['data' => ['error' => 'ไม่พบคำสั่งซื้อ'], 'status' => 404];
        }

        $status = $body['status'] ?? '';

        $validStatuses = ['pending', 'paid', 'confirmed', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['data' => ['error' => 'สถานะไม่ถูกต้อง'], 'status' => 400];
        }

        $order->status = $status;
        $this->orderRepo->update($order);

        return [
            'data' => ['order' => $order->toPublicArray()],
            'status' => 200
        ];
    }

    public function stats(array $params, array $body, ?string $userId): array
    {
        $stats = $this->orderRepo->getSalesStats();
        return [
            'data' => ['stats' => $stats],
            'status' => 200
        ];
    }
}
