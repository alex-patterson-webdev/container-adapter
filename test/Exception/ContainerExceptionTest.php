<?php

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * ContainerExceptionTest
 *
 * @package ArpTest\Container\Exception
 */
class ContainerExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @test
     */
    public function testIsInstanceOfException()
    {
        $exception = new ContainerException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @test
     */
    public function testImplementsContainerExceptionInterface()
    {
        $exception = new ContainerException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }

}
