<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/Registration.php';

class RegistrationTest extends TestCase
{
    public function testSuccessfulRegistration()
    {
        $reg = new Registration();
        $this->assertTrue($reg->register('user@example.com', 'secret123'));
    }

    public function testInvalidEmailRegistration()
    {
        $reg = new Registration();
        $this->assertFalse($reg->register('invalid-email', 'secret123'));
    }

    public function testShortPasswordRegistration()
    {
        $reg = new Registration();
        $this->assertFalse($reg->register('user@example.com', '123'));
    }
}
