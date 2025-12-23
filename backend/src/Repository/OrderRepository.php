<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Entity\Order;

class OrderRepository
{
    public function findAll(array $filters = []): array
    {
        $query = [];
        if (!empty($filters['userId'])) {
            $query['userId'] = $filters['userId'];
        }
        if (!empty($filters['status'])) {
            $query['status'] = $filters['status'];
        }

        $docs = Database::find('orders', $query, ['sort' => ['createdAt' => -1]]);
        $orders = [];
        foreach ($docs as $doc) {
            $orders[] = Order::fromDocument($doc);
        }
        return $orders;
    }

    public function findById(string $id): ?Order
    {
        $doc = Database::findOne('orders', ['_id' => Database::objectId($id)]);
        return $doc ? Order::fromDocument($doc) : null;
    }

    public function findByUserId(string $userId): array
    {
        $docs = Database::find('orders', ['userId' => $userId], ['sort' => ['createdAt' => -1]]);
        $orders = [];
        foreach ($docs as $doc) {
            $orders[] = Order::fromDocument($doc);
        }
        return $orders;
    }

    public function create(Order $order): string
    {
        $data = $order->toArray();
        unset($data['_id']);
        return Database::insertOne('orders', $data);
    }

    public function update(Order $order): bool
    {
        $data = $order->toArray();
        $data['updatedAt'] = date('Y-m-d H:i:s');
        unset($data['_id']);
        Database::updateOne('orders', ['_id' => Database::objectId($order->id)], $data);
        return true;
    }

    public function getSalesStats(): array
    {
        $orders = Database::find('orders', ['status' => 'completed']);
        
        $totalRevenue = 0;
        $totalOrders = count($orders);
        $totalItems = 0;
        $productSales = [];
        $dailySales = [];

        foreach ($orders as $order) {
            $totalRevenue += (float) $order['total'];
            $items = is_object($order['items']) ? (array) $order['items'] : ($order['items'] ?? []);
            
            foreach ($items as $item) {
                $item = is_object($item) ? (array) $item : $item;
                $totalItems += (int) ($item['quantity'] ?? 1);
                
                $productId = $item['productId'] ?? '';
                if ($productId) {
                    if (!isset($productSales[$productId])) {
                        $productSales[$productId] = [
                            'productId' => $productId,
                            'name' => $item['name'] ?? 'Unknown',
                            'quantity' => 0,
                            'revenue' => 0
                        ];
                    }
                    $productSales[$productId]['quantity'] += (int) ($item['quantity'] ?? 1);
                    $productSales[$productId]['revenue'] += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
                }
            }

            $date = substr($order['createdAt'] ?? date('Y-m-d'), 0, 10);
            if (!isset($dailySales[$date])) {
                $dailySales[$date] = ['date' => $date, 'revenue' => 0, 'orders' => 0];
            }
            $dailySales[$date]['revenue'] += (float) $order['total'];
            $dailySales[$date]['orders']++;
        }

        // Get pending orders count
        $pendingOrders = Database::find('orders', ['status' => 'pending']);
        $paidOrders = Database::find('orders', ['status' => 'paid']);

        return [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'totalItems' => $totalItems,
            'pendingOrders' => count($pendingOrders),
            'paidOrders' => count($paidOrders),
            'productSales' => array_values($productSales),
            'dailySales' => array_values($dailySales)
        ];
    }
}
