<?php

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * NotFoundExceptionTest
 *
 * @package ArpTest\Container\Exception
 */
class NotFoundExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @test
     */
    public function testIsInstanceOfException()
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @test
     */
    public function testImplementsContainerExceptionInterface()
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }

    /**
     * Ensure that the exception implements NotFoundExceptionInterface.
     *
     * @test
     */
    public function testImplementsNotFoundExceptionInterface()
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }
}
