<?php

namespace App\Services;

readonly class AuthTokenService
{
    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return [
            'userId' => 150,
        ];
    }
}
