<?php

declare(strict_types=1);

namespace ArpTest\Container\ContainerTest;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Container;
use Arp\Container\Exception\ContainerException;
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
     * Ensure that the service provider will have the containers adapter passed to it
     * when calling registerServices().
     *
     * @covers \Arp\Container\Container::registerServices
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
