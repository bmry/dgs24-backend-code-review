<?php

namespace App\Validator;

use App\Exception\ValidationException;

class MessageValidator
{
    public function validateText(mixed $text): string
    {
        if (!is_string($text) || !$text || strlen($text) > 255) {
            throw new ValidationException(
                'The "text" field is required and must not exceed 255 characters.'
            );
        }

        return $text;
    }
}
