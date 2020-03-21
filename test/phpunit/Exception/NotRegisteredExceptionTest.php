<?php

declare(strict_types=1);

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\NotRegisteredException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Exception
 */
final class NotRegisteredExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @covers \Arp\Container\Exception\NotRegisteredException
     */
    public function testIsInstanceOfException(): void
    {
        $exception = new NotRegisteredException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @covers \Arp\Container\Exception\NotRegisteredException
     */
    public function testImplementsContainerExceptionInterface(): void
    {
        $exception = new NotRegisteredException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }
}
