<?php

declare(strict_types=1);

namespace Arp\Container\Factory;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Container;
use Arp\Factory\Exception\FactoryException;
use Arp\Factory\FactoryInterface;

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

        return new Container($config['adapter']);
    }
}
