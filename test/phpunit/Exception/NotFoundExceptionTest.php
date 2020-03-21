<?php

declare(strict_types=1);

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Exception
 */
final class NotFoundExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @covers \Arp\Container\Exception\NotFoundException
     */
    public function testIsInstanceOfException(): void
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @covers \Arp\Container\Exception\NotFoundException
     */
    public function testImplementsContainerExceptionInterface(): void
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }

    /**
     * Ensure that the exception implements NotFoundExceptionInterface.
     *
     * @covers \Arp\Container\Exception\NotFoundException
     */
    public function testImplementsNotFoundExceptionInterface(): void
    {
        $exception = new NotFoundException();

        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }
}
