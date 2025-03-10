<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use Psr;
use Stancer\Interfaces\ExceptionInterface;

/**
 * Exception thrown when no payment ID was setted and an operation needs it.
 */
class MissingPaymentIdException extends BadMethodCallException implements ExceptionInterface
{
    protected static string $logLevel = Psr\Log\LogLevel::CRITICAL;

    /**
     * Return default message for that kind of exception.
     */
    public static function getDefaultMessage(): string
    {
        return 'A payment ID is mandatory. Maybe you forgot to send the payment.';
    }
}
