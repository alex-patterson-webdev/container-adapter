<?php

declare(strict_types=1);

namespace ArpTest\Container\ContainerTest;

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
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\ContainerTest
 */
final class ContainerTest extends TestCase
{
    /**
     * @var ContainerAdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
    }

    /**
     * Ensure that the container implements the PSR
     *
     * @covers \Arp\Container\Container
     */
    public function testImplementsContainerInterface(): void
    {
        $container = new Container($this->adapter, $this->logger);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * Assert AdapterException thrown by has() will be caught and rethrown as ContainerException.
     *
     * @throws ContainerException
     *
     * @covers \Arp\Container\Container::has
     */
    public function testHasWillCatchAdapterExceptionAndReThrowAsAContainerException(): void
    {
        $name = 'FooService';

        $container = new Container($this->adapter, $this->logger);

        $exceptionMessage = 'This is a test adapter exception message';
        $exception = new AdapterException($exceptionMessage);

        $this->adapter->expects($this->once())
            ->method('hasService')
            ->willThrowException($exception);

        $errorMessage = sprintf('The has() failed for service \'%s\' : %s', $name, $exceptionMessage);

        $this->logger->expects($this->once())
            ->method('debug')
            ->with($errorMessage, compact('exception', 'name'));

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode($exception->getCode());

        $container->has($name);
    }

    /**
     * Ensure that registered services will return true when calling has().
     *
     * @covers \Arp\Container\Container::has
     *
     * @throws ContainerException
     */
    public function testHasReturnsTrueForRegisteredService(): void
    {
        $container = new Container($this->adapter, $this->logger);

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
     * @covers \Arp\Container\Container::has
     *
     * @throws ContainerException
     */
    public function testHasReturnsFalseForNonRegisteredService(): void
    {
        $container = new Container($this->adapter, $this->logger);

        $serviceName = 'TestService';

        $this->adapter->expects($this->once())
                      ->method('hasService')
                      ->with($serviceName)
                      ->willReturn(false);

        $this->assertFalse($container->has($serviceName));
    }

    /**
     * Assert that if the requested service to get() fails to be found a NotFoundException is thrown.
     *
     * @covers \Arp\Container\Container::get
     */
    public function testGetWillThrowNotFoundExceptionIfRequestedServiceIsNotFound(): void
    {
        $container = new Container($this->adapter, $this->logger);

        $name = 'FooService';

        $exception = new AdapterNotFoundException('This is a test exception message', 999);

        $this->adapter->expects($this->once())
            ->method('getService')
            ->with($name)
            ->willThrowException($exception);

        $errorMessage = sprintf('The service \'%s\' could not be found', $name);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, compact('exception', 'name'));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(999);

        $container->get($name);
    }

    /**
     * Assert that NotFoundException's thrown by the adapter are caught, logged and rethrown as
     * container NotFoundException's.
     *
     * @covers \Arp\Container\Container::get
     */
    public function testGetWillThrowContainerExceptionIfGetServiceFails(): void
    {
        $container = new Container($this->adapter, $this->logger);

        $name = 'FooService';

        $exceptionMessage = 'This is a test exception message';
        $exception = new AdapterException($exceptionMessage, 888);

        $this->adapter->expects($this->once())
                      ->method('getService')
                      ->with($name)
                      ->willThrowException($exception);

        $errorMessage = sprintf('The get() failed for service \'%s\' : %s', $name, $exceptionMessage);

        $this->logger->expects($this->once())
                     ->method('error')
                     ->with($errorMessage, compact('exception', 'name'));

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(888);

        $container->get($name);
    }

    /**
     * Ensure that calls to get() will return a registered service from the adapter.
     *
     * @covers \Arp\Container\Container::get
     */
    public function testGetWillReturnRegisteredService(): void
    {
        $container = new Container($this->adapter, $this->logger);

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
     * @covers \Arp\Container\Container::registerServices
     *
     * @throws ContainerException
     */
    public function testRegisterServiceWillCatchAndRethrowServiceProviderExceptionsAsContainerException(): void
    {
        $container = new Container($this->adapter, $this->logger);

        /** @var ServiceProviderInterface|MockObject $serviceProvider */
        $serviceProvider = $this->getMockForAbstractClass(ServiceProviderInterface::class);

        $exceptionMessage = 'This is a test service provider exception message';
        $exception = new ServiceProviderException($exceptionMessage, 777);

        $serviceProvider->expects($this->once())
            ->method('registerServices')
            ->with($this->adapter)
            ->willThrowException($exception);

        $errorMessage = sprintf('Failed to register service provider : %s', $exceptionMessage);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, ['exception' => $exception, 'serviceProvider' => get_class($serviceProvider)]);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(777);

        $container->registerServices($serviceProvider);
    }


    /**
     * Ensure that the service provider will have the containers adapter passed to it
     * when calling registerServices().
     *
     * @covers \Arp\Container\Container::registerServices
     *
     * @throws ContainerException
     */
    public function testRegisterServicesWillPassAdapterToProvidedServiceProvider(): void
    {
        $container = new Container($this->adapter, $this->logger);

        /** @var ServiceProviderInterface|MockObject $serviceProvider */
        $serviceProvider = $this->getMockForAbstractClass(ServiceProviderInterface::class);

        $serviceProvider->expects($this->once())
                        ->method('registerServices')
                        ->with($this->adapter);

        $this->assertSame($container, $container->registerServices($serviceProvider));
    }
}
