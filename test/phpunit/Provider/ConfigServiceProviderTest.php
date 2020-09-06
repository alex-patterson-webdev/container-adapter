<?php

declare(strict_types=1);

namespace ArpTest\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\FactoryClassAwareInterface;
use Arp\Container\Factory\ServiceFactoryInterface;
use Arp\Container\Provider\ConfigServiceProvider;
use Arp\Container\Provider\Exception\NotSupportedException;
use Arp\Container\Provider\Exception\ServiceProviderException;
use Arp\Container\Provider\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Arp\Container\Provider\ConfigServiceProvider
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Provider
 */
final class ConfigServiceProviderTest extends TestCase
{
    /**
     * Assert that the class implements ServiceProviderInterface.
     */
    public function testImplementsServiceProviderInterface(): void
    {
        $serviceProvider = new ConfigServiceProvider([]);

        $this->assertInstanceOf(ServiceProviderInterface::class, $serviceProvider);
    }

    /**
     * Assert that a NotSupportedException will be thrown if we try to set a string factory class with an adapter
     * that does not implement FactoryClassAwareInterface
     *
     * @throws ServiceProviderException
     */
    public function testUsingStringFactoryWithNonFactoryClassAwareAdapterWillThrowNotSupportedException(): void
    {
        $name = 'FooService';
        $service = \stdClass::class;

        $serviceProvider = new ConfigServiceProvider(
            [
                'factories' => [
                    $name => $service
                ],
            ]
        );

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The adapter \'%s\' does not support the registration of string factory classes \'%s\'',
                get_class($adapter),
                $service
            )
        );

        $serviceProvider->registerServices($adapter);
    }

    /**
     * Assert that invalid factories will raise a ServiceProviderException
     *
     * @throws ServiceProviderException
     */
    public function testRegisterServicesWillThrowServiceProviderExceptionIfProvidedFactoryIsInvalid(): void
    {
        $serviceName = 'FooService';
        $serviceFactory = false; // this is our invalid factory

        $serviceProvider = new ConfigServiceProvider(
            [
                'factories' => [
                    $serviceName => $serviceFactory,
                ],
            ]
        );

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to register service \'%s\': The factory provided is not callable', $serviceName)
        );

        $serviceProvider->registerServices($adapter);
    }

    /**
     * Assert that AdapterException thrown from the adapter are caught and rethrown as ServiceProviderException.
     *
     * @throws ServiceProviderException
     */
    public function testRegisterServicesWillCatchAdapterExceptionAndRethrowAsServiceProviderException(): void
    {
        $serviceName = 'Foo';
        $serviceFactory = static function (): \stdClass {
            return new \stdClass();
        };

        $config = [
            'factories' => [
                $serviceName => $serviceFactory,
            ],
        ];

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 3456;
        $exception = new AdapterException($exceptionMessage, $exceptionCode);

        $adapter->expects($this->once())
            ->method('setFactory')
            ->with($serviceName, $serviceFactory)
            ->willThrowException($exception);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to set callable factory for service \'%s\': %s',
                $serviceName,
                $exceptionMessage
            ),
        );

        (new ConfigServiceProvider($config))->registerServices($adapter);
    }

    /**
     * Assert that register services will correctly register the provided services defined in $config.
     *
     * @param array $config The services that should be set
     *
     * @dataProvider getRegisterServicesData
     *
     * @throws ServiceProviderException
     */
    public function testRegisterServices(array $config): void
    {
        $serviceProvider = new ConfigServiceProvider($config);

        /** @var ContainerAdapterInterface|FactoryClassAwareInterface|MockObject $adapter */
        $adapter = $this->createMock(FactoryClassAwareInterface::class);

        $factories = $config['factories'] ?? [];
        $services = $config['services'] ?? [];

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
            [
                [], // empty config test
            ],

            [
                [
                    'factories' => [
                        'FooService' => static function () {
                            return 'Hi';
                        },
                    ],
                ],
            ],

            [
                [
                    'services' => [
                        'FooService' => new \stdClass(),
                        'BarService' => new \stdClass(),
                        'Baz'        => 123,
                    ],
                ],
            ],

            [
                [
                    'factories' => [
                        'BazStringService' => $this->getMockForAbstractClass(ServiceFactoryInterface::class),
                        'Bar'              => static function () {
                            return 'Test';
                        },
                    ],
                ],
            ],
        ];
    }
}
