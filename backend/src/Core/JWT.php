<?php
declare(strict_types=1);

namespace App\Core;

class JWT
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function encode(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        
        return "$header.$payload.$signature";
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid token format');
        }

        [$header, $payload, $signature] = $parts;

        // Verify signature
        $validSignature = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        
        if (!hash_equals($validSignature, $signature)) {
            throw new \InvalidArgumentException('Invalid token signature');
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        
        // Check expiry
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            throw new \InvalidArgumentException('Token has expired');
        }

        return $decodedPayload;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
