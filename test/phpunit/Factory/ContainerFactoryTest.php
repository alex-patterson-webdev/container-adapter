<?php

declare(strict_types=1);

namespace ArpTest\Container\Factory;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Factory\ContainerFactory;
use Arp\Factory\Exception\FactoryException;
use Arp\Factory\FactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Container\Factory
 */
final class ContainerFactoryTest extends TestCase
{
    /**
     * Assert that the container factory implements FactoryInterface
     *
     * @covers \Arp\Container\Factory\ContainerFactory
     */
    public function testImplementsFactoryInterface(): void
    {
        $factory = new ContainerFactory();

        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    /**
     * Assert that if the required 'adapter' configuration is not provided then FactoryException is thrown.
     *
     * @throws FactoryException
     *
     * @covers \Arp\Container\Factory\ContainerFactory::create
     */
    public function testCreateWillThrowFactoryExceptionIfTheAdapterConfigurationOptionIsNotProvided(): void
    {
        $factory = new ContainerFactory();

        $this->expectException(FactoryException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'adapter\' configuration option is required in \'%s\'',
            ContainerFactory::class
        ));

        $factory->create([]);
    }

    /**
     * Assert that a FactoryException is thrown from create if the provided adapter is not of of
     * type ContainerAdapterInterface.
     *
     * @covers \Arp\Container\Factory\ContainerFactory::create
     *
     * @throws FactoryException
     */
    public function testCreateWillThrowFactoryExceptionIfProvidedAdapterIsInvalid(): void
    {
        $factory = new ContainerFactory();

        $config = [
            'adapter' => 123, // invalid adapter!
        ];

        $this->expectException(FactoryException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'adapter\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
            ContainerAdapterInterface::class,
            gettype($config['adapter']),
            ContainerFactory::class
        ));

        $factory->create($config);
    }

    /**
     * Assert that a FactoryException is thrown if providing an invalid instance of the logger to create().
     *
     * @covers \Arp\Container\Factory\ContainerFactory::create
     *
     * @throws FactoryException
     */
    public function testCreateWillThrowFactoryExceptionIfProvidedLoggerIsInvalid(): void
    {
        $factory = new ContainerFactory();

        $logger = new \stdClass();
        $config = [
            'adapter' => $this->getMockForAbstractClass(ContainerAdapterInterface::class),
            'logger' => $logger, // invalid logger!
        ];

        $this->expectException(FactoryException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'logger\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
            LoggerInterface::class,
            get_class($logger),
            ContainerFactory::class
        ));

        $factory->create($config);
    }
}
