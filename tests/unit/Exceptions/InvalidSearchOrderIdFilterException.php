<?php

namespace Stancer\tests\unit\Exceptions;

use Psr;
use Stancer;

class InvalidSearchOrderIdFilterException extends Stancer\Tests\atoum
{
    public function testClass()
    {
        $this
            ->currentlyTestedClass
                ->extends(Stancer\Exceptions\InvalidSearchFilterException::class)
        ;
    }

    public function testGetDefaultMessage()
    {
        $this
            ->if($class = $this->testedClass->getClass())
            ->then
                ->string($class::getDefaultMessage())
                    ->isIdenticalTo('Invalid order ID.')
        ;
    }

    public function testGetLogLevel()
    {
        $this
            ->if($class = $this->testedClass->getClass())
            ->then
                ->string($class::getLogLevel())
                    ->isIdenticalTo(Psr\Log\logLevel::DEBUG)
        ;
    }
}
