<?php
declare(strict_types=1);

namespace App\Core;

use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\BulkWrite;
use MongoDB\BSON\ObjectId;

/**
 * MongoDB Database Class using native PHP MongoDB driver
 */
class Database
{
    private static ?Manager $manager = null;
    private static string $dbName = '';

    public static function init(string $uri, string $dbName): void
    {
        self::$dbName = $dbName;
        self::$manager = new Manager($uri);
    }

    public static function getManager(): Manager
    {
        if (self::$manager === null) {
            throw new \RuntimeException('Database not initialized');
        }
        return self::$manager;
    }

    public static function find(string $collection, array $filter = [], array $options = []): array
    {
        $manager = self::getManager();

        // Convert string IDs to ObjectId in filter
        if (isset($filter['_id']) && is_string($filter['_id'])) {
            try {
                $filter['_id'] = new ObjectId($filter['_id']);
            } catch (\Exception $e) {
                // Keep as string if not valid ObjectId
            }
        }

        $query = new Query($filter, $options);
        $cursor = $manager->executeQuery(self::$dbName . '.' . $collection, $query);

        $results = [];
        foreach ($cursor as $doc) {
            $arr = (array) $doc;
            // Convert ObjectId to string
            if (isset($arr['_id']) && $arr['_id'] instanceof ObjectId) {
                $arr['_id'] = (string) $arr['_id'];
            }
            $results[] = $arr;
        }
        return $results;
    }

    public static function findOne(string $collection, array $filter = []): ?array
    {
        $results = self::find($collection, $filter, ['limit' => 1]);
        return $results[0] ?? null;
    }

    public static function insertOne(string $collection, array $document): string
    {
        $manager = self::getManager();
        $bulk = new BulkWrite();

        // Generate new ObjectId
        $id = new ObjectId();
        $document['_id'] = $id;

        $bulk->insert($document);
        $manager->executeBulkWrite(self::$dbName . '.' . $collection, $bulk);

        return (string) $id;
    }

    public static function updateOne(string $collection, array $filter, array $update): int
    {
        $manager = self::getManager();

        // Convert string ID to ObjectId in filter
        if (isset($filter['_id']) && is_string($filter['_id'])) {
            try {
                $filter['_id'] = new ObjectId($filter['_id']);
            } catch (\Exception $e) {
                // Keep as string
            }
        }

        $bulk = new BulkWrite();
        $bulk->update($filter, ['$set' => $update], ['multi' => false]);
        $result = $manager->executeBulkWrite(self::$dbName . '.' . $collection, $bulk);

        return $result->getModifiedCount();
    }

    public static function deleteOne(string $collection, array $filter): int
    {
        $manager = self::getManager();

        // Convert string ID to ObjectId in filter
        if (isset($filter['_id']) && is_string($filter['_id'])) {
            try {
                $filter['_id'] = new ObjectId($filter['_id']);
            } catch (\Exception $e) {
                // Keep as string
            }
        }

        $bulk = new BulkWrite();
        $bulk->delete($filter, ['limit' => 1]);
        $result = $manager->executeBulkWrite(self::$dbName . '.' . $collection, $bulk);

        return $result->getDeletedCount();
    }

    public static function deleteMany(string $collection, array $filter): int
    {
        $manager = self::getManager();

        $bulk = new BulkWrite();
        $bulk->delete($filter, ['limit' => 0]); // limit 0 = delete all matching
        $result = $manager->executeBulkWrite(self::$dbName . '.' . $collection, $bulk);

        return $result->getDeletedCount();
    }

    public static function objectId(string $id): ObjectId
    {
        return new ObjectId($id);
    }
}
