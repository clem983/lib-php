<?php

namespace Stancer\Http\tests\unit;

use Psr;
use Stancer;

class Response extends Stancer\Tests\atoum
{
    use Stancer\Tests\Provider\Http;

    public function testClass()
    {
        $this
            ->currentlyTestedClass
                ->implements(Psr\Http\Message\ResponseInterface::class)
        ;
    }

    /**
     * @dataProvider httpStatusDataProvider
     *
     * @param mixed $code
     * @param mixed $status
     */
    public function test__construct($code, $status)
    {
        $this
            ->assert('Body as a string')
                ->if($body = uniqid())
                ->then
                    ->object($this->newTestedInstance($code, $body))
                        ->isInstanceOfTestedClass

                    ->object($this->testedInstance->getBody())
                        ->isInstanceOf(Stancer\Http\Stream::class)

                    ->castToString($this->testedInstance->getBody())
                        ->isIdenticalTo($body)

            ->assert('Body as an object')
                ->if($body = new Stancer\Http\Stream(uniqid()))
                ->then
                    ->object($this->newTestedInstance($code, $body))
                        ->isInstanceOfTestedClass

                    ->object($this->testedInstance->getBody())
                        ->isInstanceOf(Stancer\Http\Stream::class)
                        ->isIdenticalTo($body)

            ->assert('Body as a null value')
                ->object($this->newTestedInstance($code, null))
                    ->isInstanceOfTestedClass

                ->object($this->testedInstance->getBody())
                    ->isInstanceOf(Stancer\Http\Stream::class)

                ->castToString($this->testedInstance->getBody())
                    ->isEmpty
        ;
    }

    /**
     * @dataProvider httpStatusDataProvider
     *
     * @param mixed $code
     * @param mixed $message
     */
    public function testGetReasonPhrase($code, $message)
    {
        $this
            ->assert('Default reason')
                ->given($this->newTestedInstance($code))
                ->then
                    ->string($this->testedInstance->getReasonPhrase())
                        ->isIdenticalTo($message)

            ->assert('Custom reason')
                ->given($reason = uniqid())
                ->and($this->newTestedInstance($code, uniqid(), [], uniqid(), $reason))
                ->then
                    ->string($this->testedInstance->getReasonPhrase())
                        ->isIdenticalTo($reason)
        ;
    }

    public function testGetStatusCode()
    {
        $this
            ->given($code = rand(100, 600))
            ->and($this->newTestedInstance($code))
            ->then
                ->integer($this->testedInstance->getStatusCode())
                    ->isIdenticalTo($code)
        ;
    }

    public function testWithStatus()
    {
        $this
            ->given($code = rand(100, 600))
            ->and($body = uniqid())
            ->and($key = uniqid())
            ->and($value = uniqid())
            ->and($headers = [
                $key => [$value],
            ])
            ->and($protocol = uniqid())
            ->and($reason = uniqid())
            ->and($this->newTestedInstance($code, $body, $headers, $protocol, $reason))

            ->assert('Without changing reason')
                ->if($changes = rand(100, 600))
                ->then
                    ->object($obj = $this->testedInstance->withStatus($changes))
                        ->isInstanceOfTestedClass
                        ->isNotTestedInstance

                    ->integer($obj->getStatusCode())
                        ->isIdenticalTo($changes)

                    ->integer($this->testedInstance->getStatusCode())
                        ->isIdenticalTo($code)

                    // Check no diff on other properties
                    ->object($this->testedInstance->getBody())
                        ->isInstanceOf(Stancer\Http\Stream::class)
                        ->isIdenticalTo($obj->getBody())

                    ->castToString($this->testedInstance->getBody())
                        ->isIdenticalTo($body)

                    ->array($this->testedInstance->getHeaders())
                        ->isIdenticalTo($obj->getHeaders())
                        ->isIdenticalTo($headers)

                    ->string($this->testedInstance->getProtocolVersion())
                        ->isIdenticalTo($obj->getProtocolVersion())
                        ->isIdenticalTo($protocol)

                    ->string($this->testedInstance->getReasonPhrase())
                        ->isIdenticalTo($obj->getReasonPhrase())
                        ->isIdenticalTo($reason)

            ->assert('Without new reason')
                ->if($newCode = rand(100, 600))
                ->and($newReason = uniqid())
                ->then
                    ->object($obj = $this->testedInstance->withStatus($newCode, $newReason))
                        ->isInstanceOfTestedClass
                        ->isNotTestedInstance

                    ->integer($obj->getStatusCode())
                        ->isIdenticalTo($newCode)

                    ->string($obj->getReasonPhrase())
                        ->isIdenticalTo($newReason)

                    ->integer($this->testedInstance->getStatusCode())
                        ->isIdenticalTo($code)

                    ->string($this->testedInstance->getReasonPhrase())
                        ->isIdenticalTo($reason)

                    // Check no diff on other properties
                    ->object($this->testedInstance->getBody())
                        ->isInstanceOf(Stancer\Http\Stream::class)
                        ->isIdenticalTo($obj->getBody())

                    ->castToString($this->testedInstance->getBody())
                        ->isIdenticalTo($body)

                    ->array($this->testedInstance->getHeaders())
                        ->isIdenticalTo($obj->getHeaders())
                        ->isIdenticalTo($headers)

                    ->string($this->testedInstance->getProtocolVersion())
                        ->isIdenticalTo($obj->getProtocolVersion())
                        ->isIdenticalTo($protocol)
        ;
    }
}
