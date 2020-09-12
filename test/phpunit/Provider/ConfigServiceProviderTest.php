<?php

declare(strict_types=1);

namespace ArpTest\Container\Provider;

use Arp\Container\Adapter\AliasAwareInterface;
use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Factory\ServiceFactoryInterface;
use Arp\Container\Provider\ConfigServiceProvider;
use Arp\Container\Provider\Exception\NotSupportedException;
use Arp\Container\Provider\Exception\ServiceProviderException;
use Arp\Container\Provider\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers  \Arp\Container\Provider\ConfigServiceProvider
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Provider
 */
final class ConfigServiceProviderTest extends TestCase
{
    /**
     * @var ContainerAdapterInterface|MockObject
     */
    private $adapter;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->adapter = $this->createMock(ContainerAdapterInterface::class);
    }

    /**
     * Assert that the class implements ServiceProviderInterface.
     */
    public function testImplementsServiceProviderInterface(): void
    {
        $serviceProvider = new ConfigServiceProvider([]);

        $this->assertInstanceOf(ServiceProviderInterface::class, $serviceProvider);
    }

    /**
     * Assert that is the adapter raises an exception when executing setService() a ServiceProviderException
     * exception is thrown instead
     *
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    public function testRegisterServicesWillThrowServiceProviderExceptionIfServiceCannotBeRegistered(): void
    {
        $name = 'FooService';
        $service = new \stdClass();
        $config = [
            ConfigServiceProvider::SERVICES => [
                $name => $service,
            ],
        ];

        $exceptionMessage = 'This is a test exception message from the adapter';
        $exceptionCode = 123454;
        $exception = new AdapterException($exceptionMessage, $exceptionCode);

        $this->adapter->expects($this->once())
            ->method('setService')
            ->with($name, $service)
            ->willThrowException($exception);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(sprintf('Failed to register service \'%s\': %s', $name, $exceptionMessage));

        (new ConfigServiceProvider($config))->registerServices($this->adapter);
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
                    $name => $service,
                ],
            ]
        );

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The adapter \'%s\' does not support the registration of string factory classes \'%s\'',
                get_class($this->adapter),
                $service
            )
        );

        $serviceProvider->registerServices($this->adapter);
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

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to register service \'%s\': The factory provided is not callable', $serviceName)
        );

        $serviceProvider->registerServices($this->adapter);
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

        $exceptionMessage = 'This is a test exception message';
        $exceptionCode = 3456;
        $exception = new AdapterException($exceptionMessage, $exceptionCode);

        $this->adapter->expects($this->once())
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

        (new ConfigServiceProvider($config))->registerServices($this->adapter);
    }

    /**
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    public function testRegisterServicesWillThrowServiceProviderExceptionIfTheServiceAliasCannotBeSet(): void
    {
        $service = new \stdClass();
        $serviceName = 'FooService';
        $aliasName = 'FooAlias';

        $config = [
            'services' => [
                $serviceName => $service,
            ],
            'aliases'  => [
                $aliasName => $serviceName,
            ],
        ];

        $exceptionMessage = 'Test exception message';
        $exceptionCode = 12345;
        $exception = new AdapterException($exceptionMessage, $exceptionCode);

        /** @var AliasAwareInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(AliasAwareInterface::class);

        $adapter->expects($this->once())
            ->method('setService')
            ->with($serviceName, $service);

        $adapter->expects($this->once())
            ->method('setAlias')
            ->with($aliasName, $serviceName)
            ->willThrowException($exception);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to register alias \'%s\' for service \'%s\': %s',
                $aliasName,
                $serviceName,
                $exceptionMessage
            )
        );

        (new ConfigServiceProvider($config))->registerServices($adapter);
    }


    /**
     * Assert that register services will correctly register the provided services and factories defined in $config.
     *
     * @param array $config The services that should be set
     *
     * @dataProvider getRegisterServicesWithFactoriesAndServicesData
     *
     * @throws ServiceProviderException
     */
    public function testRegisterServicesWithFactoriesAndServices(array $config): void
    {
        $serviceProvider = new ConfigServiceProvider($config);

        $factories = $config[ConfigServiceProvider::FACTORIES] ?? [];
        $services = $config[ConfigServiceProvider::SERVICES] ?? [];

        $setFactoryArgs = $setServiceArgs = [];

        foreach ($factories as $name => $factory) {
            if (is_array($factory)) {
                $methodName = $factory[1] ?? '__invoke';
                $factory = $factory[0] ?? null;

                if (!is_callable($factory) && !$factory instanceof \Closure) {
                    $factory = [$factory, $methodName];
                }
            }

            $setFactoryArgs[] = [$name, $factory];
        }

        foreach ($services as $name => $service) {
            $setServiceArgs[] = [$name, $service];
        }

        $this->adapter->expects($this->exactly(count($setFactoryArgs)))
            ->method('setFactory')
            ->withConsecutive(...$setFactoryArgs);

        $this->adapter->expects($this->exactly(count($setServiceArgs)))
            ->method('setService')
            ->withConsecutive(...$setServiceArgs);

        $serviceProvider->registerServices($this->adapter);
    }

    /**
     * @return array
     */
    public function getRegisterServicesWithFactoriesAndServicesData(): array
    {
        return [
            [
                [], // empty config test
            ],

            [
                [
                    ConfigServiceProvider::FACTORIES => [
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

            // Array based registration for non callable factory object with custom method name 'create'
            [
                [
                    'factories' => [
                        'FooService' => [
                            new class {
                                public function create(ContainerInterface $container): \stdClass
                                {
                                    return new \stdClass();
                                }
                            },
                            'create',
                        ],
                    ],
                ],
            ],
        ];
    }
}
