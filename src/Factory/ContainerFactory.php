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
final class ContainerFactory implements FactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private FactoryInterface $adapterFactory;

    /**
     * @param FactoryInterface $adapterFactory
     */
    public function __construct(FactoryInterface $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param array $config
     *
     * @return Container
     *
     * @throws FactoryException
     */
    public function create(array $config = []): Container
    {
        $adapter = $config['adapter'] ?? null;

        if (null === $adapter) {
            throw new FactoryException(
                sprintf(
                    'The required \'adapter\' configuration option is missing in \'%s\'',
                    static::class
                )
            );
        }

        return new Container($this->createAdapter($adapter));
    }

    /**
     * @param ContainerAdapterInterface|array $adapter
     *
     * @return ContainerAdapterInterface
     *
     * @throws FactoryException
     */
    private function createAdapter($adapter): ContainerAdapterInterface
    {
        if (is_array($adapter)) {
            $adapter = $this->adapterFactory->create($adapter);
        }

        if (!$adapter instanceof ContainerAdapterInterface) {
            throw new FactoryException(
                sprintf(
                    'The \'adapter\' configuration option must be a object of type \'%s\'; \'%s\' provided in \'%s\'',
                    ContainerAdapterInterface::class,
                    (is_object($adapter) ? get_class($adapter) : gettype($adapter)),
                    static::class
                )
            );
        }

        return $adapter;
    }
}
