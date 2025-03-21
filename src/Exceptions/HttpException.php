<?php

declare(strict_types=1);

namespace Stancer\Exceptions;

use GuzzleHttp\Exception\RequestException;
use Psr;
use Stancer;

/**
 * Base exception class for all Stancer HTTP based exceptions.
 */
class HttpException extends Exception implements Stancer\Interfaces\ExceptionInterface
{
    /** @var string Default message */
    protected static string $defaultMessage = 'HTTP error';

    protected static string $logLevel = Psr\Log\LogLevel::WARNING;

    protected ?Psr\Http\Message\RequestInterface $request = null;

    protected ?Psr\Http\Message\ResponseInterface $response = null;

    /** @var string HTTP status */
    protected static string $status = '';

    /**
     * Construct the exception.
     *
     * @param string|null $message The Exception message to throw.
     * @param integer $code The Exception code.
     * @param \Throwable|null $previous The previous exception used for the exception chaining.
     */
    public function __construct(?string $message = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($previous instanceof RequestException) {
            $this->request = $previous->getRequest();
            $this->response = $previous->getResponse();
        }
    }

    /**
     * Create an instance from an array.
     *
     * @param array $params Parameters, keys must correspond to exception properties.
     *
     * @phpstan-param CreateExceptionParameters $params
     */
    public static function create(array $params = []): static
    {
        if (array_key_exists('status', $params)) {
            $class = static::getClassFromStatus($params['status']);

            if ($class !== static::class) {
                return $class::create($params);
            }
        }

        $obj = parent::create($params);

        if (array_key_exists('request', $params) && $params['request']) {
            $obj->request = $params['request'];
        }

        if (array_key_exists('response', $params) && $params['response']) {
            $obj->response = $params['response'];
        }

        return $obj;
    }

    /**
     * Return classname for a given HTTP status.
     *
     * @param integer $status HTTP status.
     */
    public static function getClassFromStatus(int $status): string
    {
        $list = [
            310 => TooManyRedirectsException::class,
            400 => BadRequestException::class,
            401 => NotAuthorizedException::class,
            402 => PaymentRequiredException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            405 => MethodNotAllowedException::class,
            406 => NotAcceptableException::class,
            407 => ProxyAuthenticationRequiredException::class,
            408 => RequestTimeoutException::class,
            409 => ConflictException::class,
            410 => GoneException::class,
            500 => InternalServerErrorException::class,
        ];

        if (array_key_exists($status, $list)) {
            return $list[$status];
        }

        $levels = [
            3 => RedirectionException::class,
            4 => ClientException::class,
            5 => ServerException::class,
        ];

        $level = (int) floor($status / 100);

        if (array_key_exists($level, $levels)) {
            return $levels[$level];
        }

        return static::class;
    }

    /**
     * Return default message for that kind of exception.
     */
    public static function getDefaultMessage(): string
    {
        $message = '';

        if (static::getStatus()) {
            $message = 'HTTP ' . static::getStatus() . ' - ';
        }

        return $message . static::$defaultMessage;
    }

    /**
     * Get the request that caused the exception.
     */
    public function getRequest(): ?Psr\Http\Message\RequestInterface
    {
        return $this->request;
    }

    /**
     * Get the associated response.
     */
    public function getResponse(): ?Psr\Http\Message\ResponseInterface
    {
        return $this->response;
    }

    /**
     * Return HTTP status code for this kind of exception.
     */
    public static function getStatus(): ?string
    {
        return static::$status;
    }
}
