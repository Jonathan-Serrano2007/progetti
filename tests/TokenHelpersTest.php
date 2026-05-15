<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../sito progetti/Informatica/musicare/API/token_helpers.php';

class TokenHelpersTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clear globals to avoid cross-test pollution
        $_SERVER = [];
        $_GET = [];
        parent::tearDown();
    }

    public function testExtractBearerFromAuthorizationHeader()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer abc.def.ghi';
        $token = extractBearerToken();
        $this->assertEquals('abc.def.ghi', $token);
    }

    public function testExtractFromGetFallback()
    {
        $_GET['token'] = 'fallback-token';
        $token = extractBearerToken();
        $this->assertEquals('fallback-token', $token);
    }
}
