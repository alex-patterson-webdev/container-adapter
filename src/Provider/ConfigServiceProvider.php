<?php

declare(strict_types=1);

namespace Arp\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\FactoryClassAwareInterface;
use Arp\Container\Provider\Exception\NotSupportedException;
use Arp\Container\Provider\Exception\ServiceProviderException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Provider
 */
final class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Register a collection of services with the container using the provided configuration
     *
     * @param ContainerAdapterInterface $adapter
     *
     * @throws NotSupportedException If the adapter does not support a requested operation
     * @throws ServiceProviderException If any services are unable to be registered
     */
    public function registerServices(ContainerAdapterInterface $adapter): void
    {
        try {
            $factories = $this->config['factories'] ?? [];
            foreach ($factories as $name => $factory) {
                if (is_string($factory)) {
                    if (! $adapter instanceof FactoryClassAwareInterface) {
                        throw new NotSupportedException(sprintf(
                            'The adapter class \'%s\' does not support factory class registration for service \'%s\'',
                            get_class($adapter),
                            $name
                        ));
                    }
                    $adapter->setFactoryClass($name, $factory);
                    continue;
                }
                $adapter->setFactory($name, $factory);
            }

            $services = $this->config['services'] ?? [];
            foreach ($services as $name => $service) {
                $adapter->setService($name, $service);
            }
        } catch (AdapterException $e) {
            throw new ServiceProviderException(
                sprintf('Failed to register adapter services : %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
