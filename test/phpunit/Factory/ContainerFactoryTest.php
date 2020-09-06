<?php

declare(strict_types=1);

namespace ArpTest\Container\Factory;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Factory\ContainerFactory;
use Arp\Factory\Exception\FactoryException;
use Arp\Factory\FactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @covers \Arp\Container\Factory\ContainerFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Factory
 */
final class ContainerFactoryTest extends TestCase
{
    /**
     * @var FactoryInterface|MockObject
     */
    private $adapterFactory;

    /**
     * Create test case dependencies
     */
    public function setUp(): void
    {
        $this->adapterFactory = $this->createMock(FactoryInterface::class);
    }

    /**
     * Assert that the container factory implements FactoryInterface
     */
    public function testImplementsFactoryInterface(): void
    {
        $factory = new ContainerFactory($this->adapterFactory);

        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    /**
     * Assert that if the required 'adapter' configuration is not provided then FactoryException is thrown.
     *
     * @throws FactoryException
     */
    public function testCreateWillThrowFactoryExceptionIfTheAdapterConfigurationOptionIsNotProvided(): void
    {
        $factory = new ContainerFactory($this->adapterFactory);

        $this->expectException(FactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The required \'adapter\' configuration option is missing in \'%s\'',
                ContainerFactory::class
            )
        );

        $factory->create([]);
    }

    /**
     * Assert that a FactoryException is thrown from create if the provided adapter is not of of
     * type ContainerAdapterInterface.
     *
     * @throws FactoryException
     */
    public function testCreateWillThrowFactoryExceptionIfProvidedAdapterIsInvalid(): void
    {
        $factory = new ContainerFactory($this->adapterFactory);

        $adapter = new \stdClass();

        $this->expectException(FactoryException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The \'adapter\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
                ContainerAdapterInterface::class,
                (is_object($adapter) ? get_class($adapter) : gettype($adapter)),
                ContainerFactory::class
            )
        );

        $factory->create(compact('adapter'));
    }

    /**
     * Assert that a valid Container is created when providing configuration options to create() that contains
     * and already created adapter instance
     *
     * @throws FactoryException
     */
    public function testCreateWillReturnContainerWithAdapterInstance(): void
    {
        $factory = new ContainerFactory($this->adapterFactory);

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->assertInstanceOf(ContainerInterface::class, $factory->create(compact('adapter')));
    }

    /**
     * Assert that a valid Container is created when providing configuration options to create() that contains
     * adapter configuration as an array
     *
     * @throws FactoryException
     */
    public function testCreateWillReturnContainerWithAdapterArrayConfiguration(): void
    {
        $factory = new ContainerFactory($this->adapterFactory);

        $config = [
            'adapter' => [
                'foo' => 'bar',
                'test' => new \stdClass(),
            ]
        ];

        /** @var ContainerAdapterInterface|MockObject $adapter */
        $adapter = $this->getMockForAbstractClass(ContainerAdapterInterface::class);

        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($config['adapter'])
            ->willReturn($adapter);

        $this->assertInstanceOf(ContainerInterface::class, $factory->create($config));
    }
}
