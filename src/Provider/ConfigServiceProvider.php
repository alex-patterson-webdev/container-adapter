<?php

declare(strict_types=1);

namespace Arp\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\FactoryClassAwareInterface;
use Arp\Container\Constant\ServiceConfigKey;
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
        $factories = $this->config[ServiceConfigKey::FACTORIES] ?? [];

        foreach ($factories as $name => $factory) {
            if (is_array($factory)) {
                $this->registerArrayFactory($adapter, $name, $factory);
                continue;
            }

            if (is_string($factory)) {
                $this->registerStringFactory($adapter, $name, $factory);
                continue;
            }

            $this->registerFactory($adapter, $name, $factory);
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
        if (is_object($factory)) {
            $factory = [$factory, $methodName ?? '__invoke'];
        }

        if (!is_callable($factory)) {
            throw new ServiceProviderException(
                sprintf(
                    'Failed to register service \'%s\': The factory object method \'%s\' is not callable',
                    $serviceName,
                    $methodName
                ),
            );
        }

        try {
            $adapter->setFactory($serviceName, $factory);
        } catch (AdapterException $e) {
            throw new ServiceProviderException(
                sprintf(
                    'Failed to set callable factory for service \'%s\': %s',
                    $serviceName,
                    $e->getMessage()
                ),
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
                sprintf(
                    'The factory configuration array for service \'%s\' is invalid',
                    $serviceName
                )
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
     * @param bool                      $allowFactoryLookup
     *
     * @throws NotSupportedException
     * @throws ServiceProviderException
     */
    private function registerStringFactory(
        ContainerAdapterInterface $adapter,
        string $serviceName,
        string $factory,
        string $methodName = null,
        bool $allowFactoryLookup = true
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
            if (true === $allowFactoryLookup && $adapter->hasService($factory)) {
                $factory = $adapter->getService($factory);
            }
        } catch (AdapterException $e) {
            throw new ServiceProviderException(
                sprintf(
                    'Failed to load the required factory for service \'%s\': %s',
                    $serviceName,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }

        if (!method_exists($factory, $methodName)) {
            throw new ServiceProviderException(
                sprintf(
                    'Unable to register factory class \'%s\' for service \'%s\': The method \'%s\' could not be found',
                    $factory,
                    $serviceName,
                    $methodName
                )
            );
        }

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
