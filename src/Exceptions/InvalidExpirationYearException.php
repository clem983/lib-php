<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use Stancer\Interfaces\ExceptionInterface;

/**
 * Exception thrown on miss-validation with expiration year.
 */
class InvalidExpirationYearException extends InvalidExpirationException implements ExceptionInterface
{
    /**
     * Return default message for that kind of exception.
     */
    public static function getDefaultMessage(): string
    {
        return 'Expiration year is invalid.';
    }
}
