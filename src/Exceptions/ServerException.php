<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use Psr;
use Stancer\Interfaces\ExceptionInterface;

/**
 * Exception thrown for 500 level errors.
 */
class ServerException extends HttpException implements ExceptionInterface
{
    protected static string $defaultMessage = 'Server error';

    protected static string $logLevel = Psr\Log\LogLevel::CRITICAL;

    protected static string $status = '5xx';
}
