<?php

namespace Stancer\Http\tests\unit;

use Psr;
use Stancer;

class Uri extends Stancer\Tests\atoum
{
    use Stancer\Tests\Provider\Http;

    public function cleanComponent($name, $obj)
    {
        $arr = $obj->getComponents();

        if (is_array($name)) {
            foreach ($name as $n) {
                unset($arr[$n]);
            }
        } else {
            unset($arr[$name]);
        }

        return $arr;
    }

    public function testClass()
    {
        $this
            ->currentlyTestedClass
                ->implements(Psr\Http\Message\UriInterface::class)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testCastToString($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->castToString($this->testedInstance)
                    ->isIdenticalTo($this->testedInstance->toString())
                    ->isIdenticalTo($clean)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetAuthority($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($authority = '')
            ->when(function () use (&$authority, $user, $pass, $host, $port) {
                $authority = '';

                if ($user || $pass) {
                    $authority .= $user . ($pass ? ':' : '') . $pass . '@';
                }

                $authority .= $host;

                if (!is_null($port)) {
                    $authority .= ':' . $port;
                }
            })
            ->then
                ->string($this->testedInstance->getAuthority())
                    ->isIdenticalTo($authority)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetComponents($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($fragment = $hash)
            ->and($components = array_filter(compact('fragment', 'host', 'pass', 'path', 'port', 'query', 'scheme', 'user')))
            ->then
                ->array($this->testedInstance->getComponents())
                    ->hasKeys(array_keys($components))
                    ->isEqualTo($components)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetFragment($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->string($this->testedInstance->getFragment())
                    ->isIdenticalTo($hash)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetLocalCommand($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($command = '')
            ->when(function () use (&$command, $path, $query, $hash) {
                $command = $path;

                if ($query) {
                    $command .= '?' . $query;
                }

                if ($hash) {
                    $command .= '#' . $hash;
                }
            })
            ->then
                ->string($this->testedInstance->getLocalCommand())
                    ->isIdenticalTo($command)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetHost($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->string($this->testedInstance->getHost())
                    ->isIdenticalTo($host)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetPath($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->string($this->testedInstance->getPath())
                    ->isIdenticalTo($path)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetPort($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->variable($this->testedInstance->getPort())
                    ->isIdenticalTo($port)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetQuery($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->variable($this->testedInstance->getQuery())
                    ->isIdenticalTo($query)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetScheme($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->then
                ->string($this->testedInstance->getScheme())
                    ->isIdenticalTo($scheme)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testGetUserInfo($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($info = $user . ($pass ? ':' : '') . $pass)
            ->then
                ->string($this->testedInstance->getUserInfo())
                    ->isIdenticalTo($info)
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithFragment($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($fragment = uniqid())
            ->then
                ->string($this->testedInstance->getFragment())
                    ->isIdenticalTo($hash)

                ->object($object = $this->testedInstance->withFragment($fragment))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->string($this->testedInstance->getFragment())
                    ->isIdenticalTo($hash)

                ->string($object->getFragment())
                    ->isIdenticalTo($fragment)

                ->array($this->cleanComponent('fragment', $object))
                    ->isEqualTo($this->cleanComponent('fragment', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithHost($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($newHost = uniqid())
            ->then
                ->string($this->testedInstance->getHost())
                    ->isIdenticalTo($host)

                ->object($object = $this->testedInstance->withHost($newHost))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->string($this->testedInstance->getHost())
                    ->isIdenticalTo($host)

                ->string($object->getHost())
                    ->isIdenticalTo($newHost)

                ->array($this->cleanComponent('host', $object))
                    ->isEqualTo($this->cleanComponent('host', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithPath($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($newPath = uniqid())
            ->then
                ->string($this->testedInstance->getPath())
                    ->isIdenticalTo($path)

                ->object($object = $this->testedInstance->withPath($newPath))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->string($this->testedInstance->getPath())
                    ->isIdenticalTo($path)

                ->string($object->getPath())
                    ->isIdenticalTo($newPath)

                ->array($this->cleanComponent('path', $object))
                    ->isEqualTo($this->cleanComponent('path', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithPort($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($newPort = rand(1, 65535))
            ->then
                ->variable($this->testedInstance->getPort())
                    ->isIdenticalTo($port)

                ->object($object = $this->testedInstance->withPort($newPort))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->variable($this->testedInstance->getPort())
                    ->isIdenticalTo($port)

                ->integer($object->getPort())
                    ->isIdenticalTo($newPort)

                ->array($this->cleanComponent('port', $object))
                    ->isEqualTo($this->cleanComponent('port', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithQuery($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($newQuery = uniqid())
            ->then
                ->string($this->testedInstance->getQuery())
                    ->isIdenticalTo($query)

                ->object($object = $this->testedInstance->withQuery($newQuery))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->string($this->testedInstance->getQuery())
                    ->isIdenticalTo($query)

                ->string($object->getQuery())
                    ->isIdenticalTo($newQuery)

                ->array($this->cleanComponent('query', $object))
                    ->isEqualTo($this->cleanComponent('query', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithScheme($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($newScheme = uniqid())
            ->then
                ->string($this->testedInstance->getScheme())
                    ->isIdenticalTo($scheme)

                ->object($object = $this->testedInstance->withScheme($newScheme))
                    ->isInstanceOfTestedClass
                    ->isNotTestedInstance

                ->string($this->testedInstance->getScheme())
                    ->isIdenticalTo($scheme)

                ->string($object->getScheme())
                    ->isIdenticalTo($newScheme)

                ->array($this->cleanComponent('scheme', $object))
                    ->isEqualTo($this->cleanComponent('scheme', $this->testedInstance))
        ;
    }

    /**
     * @dataProvider urlProvider
     *
     * @param mixed $uri
     * @param mixed $scheme
     * @param mixed $host
     * @param mixed $port
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $path
     * @param mixed $query
     * @param mixed $hash
     * @param mixed $clean
     */
    public function testWithUserInfo($uri, $scheme, $host, $port, $user, $pass, $path, $query, $hash, $clean)
    {
        $this
            ->if($this->newTestedInstance($uri))
            ->and($info = $user . ($pass ? ':' : '') . $pass)

            ->and($newUser = uniqid())
            ->and($newPass = uniqid())
            ->and($newInfo = $newUser . ($newPass ? ':' : '') . $newPass)
            ->then
                ->string($this->testedInstance->getUserInfo())
                    ->isIdenticalTo($info)

                ->assert('With user and pass')
                    ->object($object = $this->testedInstance->withUserInfo($newUser, $newPass))
                        ->isInstanceOfTestedClass
                        ->isNotTestedInstance

                    ->string($this->testedInstance->getUserInfo())
                        ->isIdenticalTo($info)

                    ->string($object->getUserInfo())
                        ->isIdenticalTo($newInfo)

                    ->array($this->cleanComponent(['user', 'pass'], $object))
                        ->isEqualTo($this->cleanComponent(['user', 'pass'], $this->testedInstance))
        ;
    }
}
