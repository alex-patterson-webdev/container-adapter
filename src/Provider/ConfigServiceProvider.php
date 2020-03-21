<?php

declare(strict_types=1);

namespace Arp\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\FactoryClassAwareInterface;
use Arp\Container\Exception\ContainerException;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Provider
 */
final class ConfigServiceProvider implements ServiceProviderInterface
{
    public const CONFIG_FACTORIES       = 'factories';
    public const CONFIG_FACTORY_CLASSES = 'factory_classes';
    public const CONFIG_SERVICES        = 'services';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Register a collection of services with the container.
     *
     * @param ContainerAdapterInterface $adapter
     *
     * @throws ContainerExceptionInterface
     */
    public function registerServices(ContainerAdapterInterface $adapter): void
    {
        try {
            $factories = $this->config[static::CONFIG_FACTORIES] ?? [];
            foreach ($factories as $name => $factory) {
                $adapter->setFactory($name, $factory);
            }

            $factoryClasses = $this->config[static::CONFIG_FACTORY_CLASSES] ?? [];
            if (! empty($factoryClasses) && $adapter instanceof FactoryClassAwareInterface) {
                foreach ($factoryClasses as $name => $factoryClassName) {
                    $adapter->setFactoryClass($name, $factoryClassName);
                }
            }

            $services = $this->config[static::CONFIG_SERVICES] ?? [];
            foreach ($services as $name => $service) {
                $adapter->setService($name, $service);
            }
        } catch (AdapterException $e) {
            throw new ContainerException(
                sprintf('Failed to register adapter services : %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
