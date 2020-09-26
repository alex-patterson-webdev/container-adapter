<?php

declare(strict_types=1);

namespace ArpTest\Container;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException as AdapterNotFoundException;
use Arp\Container\Container;
use Arp\Container\Exception\ContainerException;
use Arp\Container\Exception\NotFoundException;
use Arp\Container\Provider\Exception\ServiceProviderException;
use Arp\Container\Provider\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @covers  \Arp\Container\Container
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container
 */
final class ContainerTest extends TestCase
{
    /**
     * @var ContainerAdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);
    }

    /**
     * Ensure that the container implements the PSR
     */
    public function testImplementsContainerInterface(): void
    {
        $container = new Container($this->adapter);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * Assert AdapterException thrown by has() will be caught and rethrown as ContainerException.
     *
     * @throws ContainerException
     */
    public function testHasWillCatchAdapterExceptionAndReThrowAsAContainerException(): void
    {
        $name = 'FooService';

        $container = new Container($this->adapter);

        $exceptionMessage = 'This is a test adapter exception message';
        $exception = new AdapterException($exceptionMessage);

        $this->adapter->expects($this->once())
            ->method('hasService')
            ->willThrowException($exception);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exception->getCode());

        $container->has($name);
    }

    /**
     * Ensure that registered services will return true when calling has().
     *
     * @throws ContainerException
     */
    public function testHasReturnsTrueForRegisteredService(): void
    {
        $container = new Container($this->adapter);

        $serviceName = 'TestService';

        $this->adapter->expects($this->once())
            ->method('hasService')
            ->with($serviceName)
            ->willReturn(true);

        $this->assertTrue($container->has($serviceName));
    }

    /**
     * Ensure that non-registered services will return false when calling has().
     *
     * @throws ContainerException
     */
    public function testHasReturnsFalseForNonRegisteredService(): void
    {
        $container = new Container($this->adapter);

        $serviceName = 'TestService';

        $this->adapter->expects($this->once())
            ->method('hasService')
            ->with($serviceName)
            ->willReturn(false);

        $this->assertFalse($container->has($serviceName));
    }

    /**
     * Assert that if the requested service to get() fails to be found a NotFoundException is thrown.
     */
    public function testGetWillThrowNotFoundExceptionIfRequestedServiceIsNotFound(): void
    {
        $container = new Container($this->adapter);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 999;
        $exception = new AdapterNotFoundException($exceptionMessage, $exceptionCode);

        $this->adapter->expects($this->once())
            ->method('getService')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $container->get($name);
    }

    /**
     * Assert that NotFoundException's thrown by the adapter are caught, logged and rethrown as
     * container NotFoundException's.
     */
    public function testGetWillThrowContainerExceptionIfGetServiceFails(): void
    {
        $container = new Container($this->adapter);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 888;
        $exception = new AdapterException($exceptionMessage, $exceptionCode);

        $this->adapter->expects($this->once())
            ->method('getService')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $container->get($name);
    }

    /**
     * Ensure that calls to get() will return a registered service from the adapter.
     *
     * @throws ContainerExceptionInterface
     */
    public function testGetWillReturnRegisteredService(): void
    {
        $container = new Container($this->adapter);

        $serviceName = 'TestService';
        $service = new \stdClass();

        $this->adapter->expects($this->once())
            ->method('getService')
            ->with($serviceName)
            ->willReturn($service);

        $this->assertSame($service, $container->get($serviceName));
    }

    /**
     * Assert that AdapterException's that are thrown when registering services are caught, logged and rethrow
     * as ContainerException.
     *
     * @throws ContainerException
     */
    public function testRegisterServiceWillCatchAndRethrowServiceProviderExceptionsAsContainerException(): void
    {
        $container = new Container($this->adapter);

        /** @var ServiceProviderInterface|MockObject $serviceProvider */
        $serviceProvider = $this->getMockForAbstractClass(ServiceProviderInterface::class);

        $exceptionMessage = 'This is a test service provider exception message';
        $exceptionCode = 777;
        $exception = new ServiceProviderException($exceptionMessage, $exceptionCode);

        $serviceProvider->expects($this->once())
            ->method('registerServices')
            ->with($this->adapter)
            ->willThrowException($exception);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($exceptionCode);

        $container->registerServices($serviceProvider);
    }

    /**
     * Ensure that the service provider will have the containers adapter passed to it
     * when calling registerServices().
     *
     * @throws ContainerException
     */
    public function testRegisterServicesWillPassAdapterToProvidedServiceProvider(): void
    {
        $container = new Container($this->adapter);

        /** @var ServiceProviderInterface|MockObject $serviceProvider */
        $serviceProvider = $this->getMockForAbstractClass(ServiceProviderInterface::class);

        $serviceProvider->expects($this->once())
            ->method('registerServices')
            ->with($this->adapter);

        $container->registerServices($serviceProvider);
    }
}
