<?php

declare(strict_types=1);

namespace ArpTest\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\FactoryClassAwareInterface;
use Arp\Container\Provider\ConfigServiceProvider;
use Arp\Container\Provider\Exception\NotSupportedException;
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
     * Assert that a NotSupportedException will be thrown if we tru to set a string factory class with an adapter
     * that does not implement FactoryClassAwareInterface
     *
     * @throws ServiceProviderException
     *
     * @covers \Arp\Container\Provider\ConfigServiceProvider::registerServices
     */
    public function testUsingStringFactoryWithNonFactoryClassAwareAdapterWillThrowNotSupportedException(): void
    {
        $name = 'FooService';
        $service = \stdClass::class;

        $serviceProvider = new ConfigServiceProvider([
            'factories' => [$name => $service]
        ]);

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(sprintf(
            'The adapter class \'%s\' does not support factory class registration for service \'%s\'',
            get_class($adapter),
            $name
        ));

        $serviceProvider->registerServices($adapter);
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

    /**
     * Assert that AdapterException thrown from the adapter are caught and rethrown as ServiceProviderException.
     *
     * @throws ServiceProviderException
     *
     * @covers \Arp\Container\Provider\ConfigServiceProvider::registerServices
     */
    public function testRegisterServicesWillCatchAdapterExceptionAndRethrowAsServiceProviderException(): void
    {
        $serviceName = 'Foo';
        $serviceFunc = static function (): \stdClass {
            return new \stdClass();
        };

        $config = [
            'factories' => [
                $serviceName => $serviceFunc,
            ],
        ];

        $serviceProvider = new ConfigServiceProvider($config);

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $exceptionMessage = 'This is a test exception message';
        $exception = new AdapterException($exceptionMessage, 123);

        $adapter->expects($this->once())
            ->method('setFactory')
            ->with($serviceName, $serviceFunc)
            ->willThrowException($exception);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage(sprintf('Failed to register adapter services : %s', $exceptionMessage));
        $this->expectExceptionCode(123);

        $serviceProvider->registerServices($adapter);
    }

    /**
     * Assert that register services will correctly register the provided services defined in $config.
     *
     * @param array $config The services that should be set
     *
     * @dataProvider getRegisterServicesData
     * @covers \Arp\Container\Provider\ConfigServiceProvider::registerServices
     *
     * @throws ServiceProviderException
     */
    public function testRegisterServices(array $config): void
    {
        $serviceProvider = new ConfigServiceProvider($config);

        /** @var ContainerAdapterInterface|FactoryClassAwareInterface|MockObject $adapter */
        $adapter = $this->createMock(FactoryClassAwareInterface::class);

        $factories = $config['factories'] ?? [];
        $services  = $config['services']  ?? [];

        $setFactoryArgs = $setServiceArgs = $setClassArgs = [];

        foreach ($factories as $name => $factory) {
            if (is_string($factory)) {
                $setClassArgs[] = [$name, $factory];
            } else {
                $setFactoryArgs[] = [$name, $factory];
            }
        }
        foreach ($services as $name => $service) {
            $setServiceArgs[] = [$name, $service];
        }

        $adapter->expects($this->exactly(count($setFactoryArgs)))
                ->method('setFactory')
                ->withConsecutive(...$setFactoryArgs);

        $adapter->expects($this->exactly(count($setClassArgs)))
                ->method('setFactoryClass')
                ->withConsecutive(...$setClassArgs);

        $adapter->expects($this->exactly(count($setServiceArgs)))
                ->method('setService')
                ->withConsecutive(...$setServiceArgs);

        $serviceProvider->registerServices($adapter);
    }

    /**
     * @return array
     */
    public function getRegisterServicesData(): array
    {
        return [
//            [
//                [], // empty config test
//            ],
//
//            [
//                [
//                    'factories' => [
//                        'FooService' => static function () {
//                            return 'Hi';
//                        },
//                    ],
//                ]
//            ],
//
//            [
//                [
//                    'services' => [
//                        'FooService' => new \stdClass(),
//                        'BarService' => new \stdClass(),
//                        'Baz' => 123,
//                    ],
//                ],
//            ],

            [
                [
                    'factories' => [
                        'BazStringService' => 'Hello',
                        'Bar' => static function () {
                            return 'Test';
                        },
                        'Foo' => 'FooFactory',
                    ],
                ],
            ],
        ];
    }
}
