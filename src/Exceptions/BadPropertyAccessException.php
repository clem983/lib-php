<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use Psr;
use Stancer\Interfaces\ExceptionInterface;

/**
 * Exception thrown if a callback refers to an undefined method or if some arguments are missing.
 */
class BadPropertyAccessException extends Exception implements ExceptionInterface
{
    /** @var string Default log level */
    protected static string $logLevel = Psr\Log\LogLevel::CRITICAL;

    /**
     * Return default message for that kind of exception.
     */
    public static function getDefaultMessage(): string
    {
        return 'Bad property access';
    }
}
