<?php

declare(strict_types=1);

namespace ArpTest\Container\Exception;

use Arp\Container\Exception\NotSupportedException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Exception
 */
final class NotSupportedExceptionTest extends TestCase
{
    /**
     * Ensure that the exception is an instance of the default PHP exception instance.
     *
     * @covers \Arp\Container\Exception\NotSupportedException
     */
    public function testIsInstanceOfException()
    {
        $exception = new NotSupportedException();

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    /**
     * Ensure that the exception implements ContainerExceptionInterface.
     *
     * @covers \Arp\Container\Exception\NotSupportedException
     */
    public function testImplementsContainerExceptionInterface()
    {
        $exception = new NotSupportedException();

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }
}
