<?php

namespace Arp\Container;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Provider\ServiceProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Container
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container
 */
class Container implements ContainerInterface
{
    /**
     * $adapter
     *
     * @var ContainerAdapterInterface
     */
    protected $adapter;

    /**
     * __construct
     *
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
     */
    public function has($name)
    {
        return $this->adapter->hasService($name);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string  $name  Identifier of the entry to look for.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get($name)
    {
        return $this->adapter->getService($name);
    }

    /**
     * Register a collection of services defined in the provided service provider.
     *
     * @param ServiceProviderInterface $serviceProvider
     *
     * @throws ContainerExceptionInterface
     */
    public function registerServices(ServiceProviderInterface $serviceProvider)
    {
        $serviceProvider->registerServices($this->adapter);
    }
}

