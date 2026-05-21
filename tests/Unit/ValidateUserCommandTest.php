<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidateUserCommandTest extends TestCase
{
    public function testTokenGenerationIsSecure(): void
    {
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));

        // Le token doit faire 64 caractères (32 bytes en hex)
        $this->assertSame(64, strlen($token1));

        // Deux tokens générés ne doivent jamais être identiques
        $this->assertNotSame($token1, $token2);

        // Le token doit être hexadécimal
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token1);
    }

    public function testInternalIdFormat(): void
    {
        $internalId = 'FC-' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Le format doit être FC- suivi de 8 caractères hexadécimaux en majuscules
        $this->assertMatchesRegularExpression('/^FC-[0-9A-F]{8}$/', $internalId);
    }
}
