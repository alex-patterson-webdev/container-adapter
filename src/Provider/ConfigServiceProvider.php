<?php

declare(strict_types=1);

namespace Arp\Container\Provider;

use Arp\Container\Adapter\AliasAwareInterface;
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
    public const ALIASES = 'aliases';
    public const FACTORIES = 'factories';
    public const SERVICES = 'services';

    /**
     * @var array
     */
    private array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Attempt to register services from configuration with the provided $adapter.
     *
     * @param ContainerAdapterInterface $adapter The adapter to register services with.
     *
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    public function registerServices(ContainerAdapterInterface $adapter): void
    {
        $services = $this->config[static::SERVICES] ?? [];
        foreach ($services as $name => $service) {
            try {
                $adapter->setService($name, $service);
            } catch (AdapterException $e) {
                throw new ServiceProviderException(
                    sprintf('Failed to register service \'%s\': %s', $name, $e->getMessage()),
                    $e->getCode(),
                    $e
                );
            }
        }

        $factories = $this->config[static::FACTORIES] ?? [];
        if (!empty($factories)) {
            $this->registerFactories($adapter, $factories);
        }

        $aliases = $this->config[static::ALIASES] ?? [];
        if (!empty($aliases) && $adapter instanceof AliasAwareInterface) {
            $this->registerAliases($adapter, $aliases);
        }
    }

    /**
     * @param ContainerAdapterInterface $adapter
     * @param array                      $factories
     *
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    private function registerFactories(ContainerAdapterInterface $adapter, array $factories): void
    {
        foreach ($factories as $name => $factory) {
            if (is_array($factory)) {
                $this->registerArrayFactory($adapter, $name, $factory);
                continue;
            }

            if (is_string($factory)) {
                if (!$adapter instanceof FactoryClassAwareInterface) {
                    throw new NotSupportedException(
                        sprintf(
                            'The adapter \'%s\' does not support the registration of string factory classes \'%s\'',
                            get_class($adapter),
                            $factory
                        )
                    );
                }

                $this->registerStringFactory($adapter, $name, $factory);
                continue;
            }

            $this->registerFactory($adapter, $name, $factory);
        }
    }

    /**
     * @param AliasAwareInterface $adapter
     * @param array               $aliases
     *
     * @throws ServiceProviderException
     */
    private function registerAliases(AliasAwareInterface $adapter, array $aliases): void
    {
        foreach ($aliases as $alias => $serviceName) {
            try {
                $adapter->setAlias($alias, $serviceName);
            } catch (AdapterException $e) {
                throw new ServiceProviderException(
                    sprintf(
                        'Failed to register alias \'%s\' for service \'%s\': %s',
                        $alias,
                        $serviceName,
                        $e->getMessage()
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    /**
     * @param ContainerAdapterInterface $adapter
     * @param string                    $serviceName
     * @param object|callable           $factory
     * @param string|null               $methodName
     *
     * @throws ServiceProviderException
     */
    private function registerFactory(
        ContainerAdapterInterface $adapter,
        string $serviceName,
        $factory,
        string $methodName = null
    ): void {
        $methodName = $methodName ?? '__invoke';

        if (!is_callable($factory) && !$factory instanceof \Closure) {
            $factory = [$factory, $methodName];
        }

        if (!is_callable($factory)) {
            throw new ServiceProviderException(
                sprintf('Failed to register service \'%s\': The factory provided is not callable', $serviceName),
            );
        }

        try {
            $adapter->setFactory($serviceName, $factory);
        } catch (AdapterException $e) {
            throw new ServiceProviderException(
                sprintf('Failed to set callable factory for service \'%s\': %s', $serviceName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Register a factory that was provided as a configuration array.
     *
     * Using the array format of [$factory, $methodName]
     *
     * $factory can be callable|object|string
     *
     * @param ContainerAdapterInterface $adapter
     * @param string                    $serviceName
     * @param array                     $factoryConfig
     *
     * @throws ServiceProviderException
     */
    private function registerArrayFactory(
        ContainerAdapterInterface $adapter,
        string $serviceName,
        array $factoryConfig
    ): void {
        $factory = $factoryConfig[0] ?? null;

        if (null === $factory) {
            throw new ServiceProviderException(
                sprintf('The factory configuration array for service \'%s\' is invalid', $serviceName)
            );
        }

        $methodName = $factoryConfig[1] ?? '__invoke';

        if (is_string($factory)) {
            $this->registerStringFactory($adapter, $serviceName, $factory, $methodName);
            return;
        }

        if (is_object($factory) || is_callable($factory)) {
            $this->registerFactory($adapter, $serviceName, $factory, $methodName);
            return;
        }

        throw new ServiceProviderException(
            sprintf('Failed to register service \'%s\': The provided array configuration is invalid', $serviceName)
        );
    }

    /**
     * Register a factory provided as a string
     *
     * @param ContainerAdapterInterface $adapter
     * @param string                    $serviceName
     * @param string                    $factory
     * @param string|null               $methodName
     *
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    private function registerStringFactory(
        ContainerAdapterInterface $adapter,
        string $serviceName,
        string $factory,
        string $methodName = null
    ): void {
        if (!$adapter instanceof FactoryClassAwareInterface) {
            throw new NotSupportedException(
                sprintf(
                    'The adapter class \'%s\' does not support factory class registration for service \'%s\'',
                    get_class($adapter),
                    $serviceName
                )
            );
        }

        $methodName ??= '__invoke';

        try {
            $adapter->setFactoryClass($serviceName, $factory, $methodName);
        } catch (AdapterException $e) {
            throw new ServiceProviderException(
                sprintf(
                    'Failed to register service \'%s\' with adapter \'%s\' using factory class \'%s\': %s',
                    $serviceName,
                    get_class($adapter),
                    $factory,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }
}
