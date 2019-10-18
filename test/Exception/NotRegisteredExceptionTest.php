<?php

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\NotRegisteredException;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * NotRegisteredExceptionTest
 *
 * @package ArpTest\Container\Exception
 */
class NotRegisteredExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @test
     */
    public function testIsInstanceOfException()
    {
        $exception = new NotRegisteredException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @test
     */
    public function testImplementsContainerExceptionInterface()
    {
        $exception = new NotRegisteredException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }
}
