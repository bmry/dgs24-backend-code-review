<?php

namespace App\Tests\Unit\Message;

use App\Exception\ValidationException;
use App\Validator\MessageValidator;
use PHPUnit\Framework\TestCase;

class MessageValidatorTest extends TestCase
{
    private MessageValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new MessageValidator();
    }

    public function testValidateTextValidText(): void
    {
        $validText = 'This is a valid message';

        $result = $this->validator->validateText($validText);

        $this->assertEquals($validText, $result);
    }

    public function testValidateTextEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "text" field is required and must not exceed 255 characters.');

        $this->validator->validateText('');
    }

    public function testValidateTextNonStringInput(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "text" field is required and must not exceed 255 characters.');

        $this->validator->validateText(123);
    }

    public function testValidateTextTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "text" field is required and must not exceed 255 characters.');

        $this->validator->validateText(str_repeat('A', 256));
    }
}
