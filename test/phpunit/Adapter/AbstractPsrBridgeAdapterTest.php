<?php

declare(strict_types=1);

namespace ArpTest\Container\Adapter;

use Arp\Container\Adapter\AbstractPsrBridgeAdapter;
use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException;
use Arp\Container\Exception\NotFoundException as PsrContainerNotFoundException;
use Arp\Container\Exception\ContainerException as PsrContainerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Adapter
 */
class AbstractPsrBridgeAdapterTest extends TestCase
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
        /** @var ContainerInterface|MockObject $container */
        $this->container = $this->getMockForAbstractClass(ContainerInterface::class);
    }

    /**
     * Assert that the class extends ContainerAdapterInterface.
     *
     * @covers \Arp\Container\Adapter\AbstractPsrBridgeAdapter
     */
    public function testImplementsContainerAdapterInterface(): void
    {
        /** @var AbstractPsrBridgeAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrBridgeAdapter::class, [$this->container]);

        $this->assertInstanceOf(ContainerAdapterInterface::class, $adapter);
    }

    /**
     * Assert that and AdapterException will be thrown if the call to hasService() cannot be completed.
     *
     * @covers \Arp\Container\Adapter\AbstractPsrBridgeAdapter::hasService
     *
     * @throws AdapterException
     */
    public function testHasServiceWillThrowAnAdapterException(): void
    {
        /** @var AbstractPsrBridgeAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrBridgeAdapter::class, [$this->container]);

        $name = 'FooService';
        $exceptionMessage = 'This is a test exception message';
        $exception = new \Exception($exceptionMessage);

        $this->container->expects($this->once())
            ->method('has')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(AdapterException::class);
        $this->expectExceptionMessage(sprintf(
            'The check for service \'%s\' failed : %s',
            $name,
            $exceptionMessage
        ));

        $adapter->hasService($name);
    }

    /**
     * Assert that the getService() method will throw a NotFoundException.
     *
     * @throws AdapterException
     * @throws NotFoundException
     */
    public function testGetServiceWillThrowNotFoundExceptionForUnknownServiceName(): void
    {
        /** @var AbstractPsrBridgeAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrBridgeAdapter::class, [$this->container]);

        $name = 'FooService';
        $exceptionMessage = 'This is a test exception message';
        $exception = new PsrContainerNotFoundException($exceptionMessage, 123);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage(sprintf(
            'The service \'%s\' could not be found : %s',
            $name,
            $exceptionMessage
        ));

        $adapter->getService($name);
    }

    /**
     * Assert that a AdapterException is thrown on error in method getService()
     *
     * @covers \Arp\Container\Adapter\AbstractPsrBridgeAdapter::getService
     *
     * @throws AdapterException
     * @throws NotFoundException
     */
    public function testGetServiceWillThrowAdapterException(): void
    {
        /** @var AbstractPsrBridgeAdapter|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AbstractPsrBridgeAdapter::class, [$this->container]);

        $name = 'FooService';
        $exceptionMessage = 'This is a test exception message';
        $exception = new PsrContainerException($exceptionMessage, 123);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(AdapterException::class);
        $this->expectExceptionCode(123);
        $this->expectExceptionMessage(sprintf(
            'The service \'%s\' was found but could not be returned : %s',
            $name,
            $exceptionMessage
        ));

        $adapter->getService($name);
    }

}
