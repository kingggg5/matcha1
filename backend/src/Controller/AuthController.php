<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\JWT;
use App\Entity\User;
use App\Repository\UserRepository;

class AuthController
{
    private array $config;
    private UserRepository $userRepo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->userRepo = new UserRepository();
    }

    public function register(array $params, array $body, ?string $userId): array
    {
        // Validate input
        if (empty($body['name']) || empty($body['email']) || empty($body['password'])) {
            return ['data' => ['error' => 'Name, email and password are required'], 'status' => 400];
        }

        if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return ['data' => ['error' => 'Invalid email format'], 'status' => 400];
        }

        if (strlen($body['password']) < 6) {
            return ['data' => ['error' => 'Password must be at least 6 characters'], 'status' => 400];
        }

        // Check if email exists
        if ($this->userRepo->findByEmail($body['email'])) {
            return ['data' => ['error' => 'Email already registered'], 'status' => 409];
        }

        // Create user
        $user = new User(
            $body['name'],
            $body['email'],
            password_hash($body['password'], PASSWORD_BCRYPT),
            $body['role'] ?? 'user'
        );

        $id = $this->userRepo->create($user);
        $user->id = $id;

        // Generate token
        $jwt = new JWT($this->config['jwt']['secret']);
        $token = $jwt->encode([
            'sub' => $id,
            'email' => $user->email,
            'role' => $user->role,
            'exp' => time() + $this->config['jwt']['expiry']
        ]);

        return [
            'data' => [
                'message' => 'Registration successful',
                'user' => $user->toPublicArray(),
                'token' => $token
            ],
            'status' => 201
        ];
    }

    public function login(array $params, array $body, ?string $userId): array
    {
        // Validate input
        if (empty($body['email']) || empty($body['password'])) {
            return ['data' => ['error' => 'Email and password are required'], 'status' => 400];
        }

        // Find user
        $user = $this->userRepo->findByEmail($body['email']);
        
        if (!$user || !password_verify($body['password'], $user->password)) {
            return ['data' => ['error' => 'Invalid credentials'], 'status' => 401];
        }

        // Generate token
        $jwt = new JWT($this->config['jwt']['secret']);
        $token = $jwt->encode([
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'exp' => time() + $this->config['jwt']['expiry']
        ]);

        return [
            'data' => [
                'message' => 'Login successful',
                'user' => $user->toPublicArray(),
                'token' => $token
            ],
            'status' => 200
        ];
    }

    public function me(array $params, array $body, ?string $userId): array
    {
        if (!$userId) {
            return ['data' => ['error' => 'Unauthorized'], 'status' => 401];
        }

        $user = $this->userRepo->findById($userId);
        
        if (!$user) {
            return ['data' => ['error' => 'User not found'], 'status' => 404];
        }

        return [
            'data' => ['user' => $user->toPublicArray()],
            'status' => 200
        ];
    }
}
