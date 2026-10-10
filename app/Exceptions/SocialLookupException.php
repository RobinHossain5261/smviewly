<?php

namespace App\Exceptions;

use RuntimeException;

class SocialLookupException extends RuntimeException
{
    public static function notConfigured(): self
    {
        return new self('Social API is not configured. Add the CheckMyAlgo API URL and token in Admin → Settings.');
    }

    public static function failed(string $message): self
    {
        return new self($message !== '' ? $message : 'The lookup failed. Please try again.');
    }
}
