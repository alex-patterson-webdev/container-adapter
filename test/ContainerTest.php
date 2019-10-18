<?php

namespace ArpTest\Container\ContainerTest;

use Arp\Container\Container;
use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Provider\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * ContainerTest
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\ContainerTest
 */
class ContainerTest extends TestCase
{
    /**
     * $adapter
     *
     * @var ContainerAdapterInterface|MockObject
     */
    protected $adapter;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);
    }

    /**
     * testImplementsContainerInterface
     *
     * Ensure that the container implements the PSR
     *
     * @test
     */
    public function testImplementsContainerInterface()
    {
        $container = new Container($this->adapter);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * Ensure that registered services will return true when calling has().
     *
     * @test
     */
    public function testHasReturnsTrueForRegisteredService()
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
     * @test
     */
    public function testHasReturnsFalseForNonRegisteredService()
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
     * Ensure that calls to get() will return a registered service from the adapter.
     *
     * @test
     */
    public function testGetWillReturnRegisteredService()
    {
        $container = new Container($this->adapter);

        $serviceName = 'TestService';
        $service     = new \stdClass;

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
     * @test
     */
    public function testRegisterServicesWillPassAdapterToProvidedServiceProvider()
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
