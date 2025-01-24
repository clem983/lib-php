<?php

namespace Stancer\tests\unit\Exceptions;

use Psr;
use Stancer;

class ConflictException extends Stancer\Tests\atoum
{
    public function testClass()
    {
        $this
            ->currentlyTestedClass
                ->extends(Stancer\Exceptions\ClientException::class)
                ->implements(Stancer\Interfaces\ExceptionInterface::class)
        ;
    }

    public function testGetDefaultMessage()
    {
        $this
            ->if($class = $this->testedClass->getClass())
            ->then
                ->string($class::getDefaultMessage())
                    ->isIdenticalTo('HTTP 409 - Conflict')
        ;
    }

    public function testGetLogLevel()
    {
        $this
            ->if($class = $this->testedClass->getClass())
            ->then
                ->string($class::getLogLevel())
                    ->isIdenticalTo(Psr\Log\logLevel::ERROR)
        ;
    }

    public function testGetStatus()
    {
        $this
            ->if($class = $this->testedClass->getClass())
            ->then
                ->string($class::getStatus())
                    ->isIdenticalTo('409')
        ;
    }
}
