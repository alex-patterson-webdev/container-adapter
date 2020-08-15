<?php

declare(strict_types=1);

namespace ArpTest\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Provider\ConfigServiceProvider;
use Arp\Container\Provider\Exception\ServiceProviderException;
use Arp\Container\Provider\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Provider
 */
final class ConfigServiceProviderTest extends TestCase
{
    /**
     * Assert that the class implements ServiceProviderInterface.
     *
     * @covers \Arp\Container\Provider\ConfigServiceProvider
     */
    public function testImplementsServiceProviderInterface(): void
    {
        $serviceProvider = new ConfigServiceProvider([]);

        $this->assertInstanceOf(ServiceProviderInterface::class, $serviceProvider);
    }

    /**
     * Assert that invalid factories will raise a ServiceProviderException
     *
     * @throws ServiceProviderException
     *
     * @covers \Arp\Container\Provider\ConfigServiceProvider::registerServices
     */
    public function testRegisterServicesWillThrowServiceProviderExceptionIfProvidedFactoryIsInvalid(): void
    {
        $serviceName = 'FooService';
        $serviceFactory = false; // this is our invalid factory

        $serviceProvider = new ConfigServiceProvider([
            'factories' => [
                $serviceName => $serviceFactory
            ]
        ]);

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $exceptionMessage = sprintf(
            'Service factories must be of type \'callable\'; \'%s\' provided for service \'%s\'',
            (is_object($serviceFactory) ? get_class($serviceFactory) : gettype($serviceFactory)),
            $serviceName
        );

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $serviceProvider->registerServices($adapter);
    }
}
