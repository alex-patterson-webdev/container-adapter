<?php

declare(strict_types=1);

namespace Arp\Container;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException;
use Arp\Container\Exception\ContainerException;
use Arp\Container\Provider\Exception\ServiceProviderException;
use Arp\Container\Provider\ServiceProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container
 */
final class Container implements ContainerInterface
{
    /**
     * @var ContainerAdapterInterface
     */
    private ContainerAdapterInterface $adapter;

    /**
     * @param ContainerAdapterInterface $adapter
     */
    public function __construct(ContainerAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($name)` returning true does not mean that `get($name)` will not throw an exception.
     * It does however mean that `get($name)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @return bool
     *
     * @throws Exception\ContainerException If the operation cannot be completed
     */
    public function has($name): bool
    {
        try {
            return $this->adapter->hasService($name);
        } catch (AdapterException $e) {
            throw new Exception\ContainerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @return mixed
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function get($name)
    {
        try {
            return $this->adapter->getService($name);
        } catch (NotFoundException $e) {
            throw new Exception\NotFoundException($e->getMessage(), $e->getCode(), $e);
        } catch (AdapterException $e) {
            throw new Exception\ContainerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Register a collection of services defined in the provided service provider.
     *
     * @param ServiceProviderInterface $serviceProvider
     *
     * @throws ContainerException
     */
    public function registerServices(ServiceProviderInterface $serviceProvider): void
    {
        try {
            $serviceProvider->registerServices($this->adapter);
        } catch (ServiceProviderException $e) {
            throw new ContainerException(
                sprintf(
                    'Failed to register service provider \'%s\': %s',
                    get_class($serviceProvider),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }
}
