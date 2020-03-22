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


    public function registerServices(ContainerAdapterInterface $adapter): void
    {
        try {
            $factories = $this->config['factories'] ?? [];

            foreach ($factories as $name => $factory) {
                if (is_string($factory)) {
                    if (! $adapter instanceof FactoryClassAwareInterface) {
                        $exceptionMessage = sprintf(
                            'The adapter class \'%s\' does not support factory class registration for service \'%s\'',
                            get_class($adapter),
                            $name
                        );
                        throw new NotSupportedException($exceptionMessage);
                    }
                    $adapter->setFactoryClass($name, $factory);
                } elseif (! is_callable($factory)) {
                    $exceptionMessage = sprintf(
                        'Service factories must be of type \'callable\'; \'%s\' provided for service \'%s\'',
                        (is_object($factory) ? get_class($factory) : gettype($factory)),
                        $name
                    );

                    throw new ServiceProviderException($exceptionMessage);
                }
                $adapter->setFactory($name, $factory);
            }

            $services = $this->config['services'] ?? [];

            foreach ($services as $name => $service) {
                $adapter->setService($name, $service);
            }
        } catch (ServiceProviderException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ServiceProviderException(
                sprintf('Failed to register adapter services : %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
