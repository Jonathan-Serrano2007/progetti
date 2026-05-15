<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../sito progetti/Informatica/musicare/API/jwt_config.php';

class JwtConfigTest extends TestCase
{
    public function testDefaultSecretUsedWhenEnvEmpty()
    {
        putenv('JWT_SECRET=');
        $secret = getJwtSecret();
        $this->assertIsString($secret);
        $this->assertGreaterThanOrEqual(32, strlen($secret));
    }

    public function testTooShortSecretThrows()
    {
        putenv('JWT_SECRET=short');
        $this->expectException(RuntimeException::class);
        getJwtSecret();
    }
}
