<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use Psr;
use Stancer\Interfaces\ExceptionInterface;

/**
 * Exception thrown on invalid description.
 */
class InvalidDescriptionException extends InvalidArgumentException implements ExceptionInterface
{
    protected static string $logLevel = Psr\Log\LogLevel::DEBUG;

    /**
     * Return default message for that kind of exception.
     */
    public static function getDefaultMessage(): string
    {
        return 'Invalid description.';
    }
}
