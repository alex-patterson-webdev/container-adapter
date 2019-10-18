<?php

namespace ArpTest\Container\Exception;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * NotSupportedException
 *
 * @package ArpTest\Container\Exception
 */
class NotSupportedException extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @test
     */
    public function testIsInstanceOfException()
    {
        $exception = new NotSupportedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @test
     */
    public function testImplementsContainerExceptionInterface()
    {
        $exception = new NotSupportedException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }
}
