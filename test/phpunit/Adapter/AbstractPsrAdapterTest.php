<?php

declare(strict_types=1);

namespace ArpTest\Container\Adapter;

use Arp\Container\Adapter\AbstractPsrAdapter;
use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException as AdapterNotFoundException;
use Arp\Container\Exception\ContainerException;
use Arp\Container\Exception\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Arp\Container\Adapter\AbstractPsrAdapter
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Adapter
 */
final class AbstractPsrAdapterTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * Set up the test case dependencies
     */
    public function setUp(): void
    {
        $this->container = $this->getMockForAbstractClass(ContainerInterface::class);
    }

    /**
     * Assert that the class extends ContainerAdapterInterface
     */
    public function testImplementsContainerAdapterInterface(): void
    {
        /** @var AbstractPsrAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrAdapter::class, [$this->container]);

        $this->assertInstanceOf(ContainerAdapterInterface::class, $adapter);
    }

    /**
     * Assert that and AdapterException will be thrown if the call to hasService() cannot be completed
     *
     * @throws AdapterException
     */
    public function testHasServiceWillThrowAnAdapterException(): void
    {
        /** @var AbstractPsrAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrAdapter::class, [$this->container]);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 456;
        $exception = new ContainerException($exceptionMessage, $exceptionCode);

        $this->container->expects($this->once())
            ->method('has')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $adapter->hasService($name);
    }

    /**
     * Assert that the getService() method will throw a NotFoundException.
     *
     * @throws AdapterException
     * @throws AdapterNotFoundException
     */
    public function testGetServiceWillThrowNotFoundExceptionForUnknownServiceName(): void
    {
        /** @var AbstractPsrAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrAdapter::class, [$this->container]);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 123;
        $exception = new NotFoundException($exceptionMessage, $exceptionCode);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(AdapterNotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $adapter->getService($name);
    }

    /**
     * Assert that a AdapterException is thrown on error in method getService()
     *
     * @throws AdapterException
     */
    public function testGetServiceWillThrowAdapterException(): void
    {
        /** @var AbstractPsrAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrAdapter::class, [$this->container]);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 987;
        $exception = new ContainerException($exceptionMessage, $exceptionCode);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $adapter->getService($name);
    }
}
