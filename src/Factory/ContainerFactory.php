<?php

declare(strict_types=1);

namespace Arp\Container\Factory;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Container;
use Arp\Factory\Exception\FactoryException;
use Arp\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Factory
 */
class ContainerFactory implements FactoryInterface
{
    /**
     * Create a new container instance
     *
     * @param array $config The optional factory configuration options
     *
     * @return Container
     *
     * @throws FactoryException If the container cannot be created
     */
    public function create(array $config = []): Container
    {
        $adapter = $config['adapter'] ?? null;
        $logger  = $config['logger']  ?? new NullLogger();

        if (null === $adapter) {
            throw new FactoryException(sprintf(
                'The \'adapter\' configuration option is required in \'%s\'',
                static::class
            ));
        }

        if (! $adapter instanceof ContainerAdapterInterface) {
            throw new FactoryException(sprintf(
                'The \'adapter\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
                ContainerAdapterInterface::class,
                (is_object($adapter) ? $adapter : gettype($adapter)),
                static::class
            ));
        }

        if (! $logger instanceof LoggerInterface) {
            throw new FactoryException(sprintf(
                'The \'logger\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
                LoggerInterface::class,
                (is_object($logger) ? $logger : gettype($logger)),
                static::class
            ));
        }

        return new Container($adapter, $logger);
    }
}
