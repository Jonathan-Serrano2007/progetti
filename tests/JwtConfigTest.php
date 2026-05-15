<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../sito progetti/Informatica/musicare/API/jwt_config.php';

class JwtConfigTest extends TestCase
{
    public function testValidSecretReturnsString()
    {
        putenv('JWT_SECRET=my-super-secret-key-that-is-long-enough-for-testing');
        $secret = getJwtSecret();
        $this->assertIsString($secret);
        $this->assertNotEmpty($secret);
    }

    public function testTooShortSecretThrows()
    {
        putenv('JWT_SECRET=short');
        $this->expectException(RuntimeException::class);
        getJwtSecret();
    }
}
