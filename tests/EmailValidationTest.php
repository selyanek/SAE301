<?php

use PHPUnit\Framework\TestCase;
use src\Utils\EmailValidator;

class EmailValidationTest extends TestCase
{
    public function testValidEmail(): void
    {
        $this->assertTrue(
            EmailValidator::isValid('selyane.khentache@uphf.fr')
        );
    }

    public function testInvalidEmail(): void
    {
        $this->assertFalse(
            EmailValidator::isValid('email-invalide')
        );
    }
}
