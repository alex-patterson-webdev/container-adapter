<?php

namespace Arp\Container\Adapter;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * AbstractAdapter
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
abstract class AbstractAdapter implements ContainerAdapterInterface
{
    /**
     * $container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * __construct
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * hasService
     *
     * Check if a service is registered with this name.
     *
     * @param string $name The name of the service to check.
     *
     * @return bool
     *
     * @throws ContainerExceptionInterface
     */
    public function hasService($name)
    {
        return $this->container->has($name);
    }

    /**
     * getService
     *
     * Return a service matching the provided name.
     *
     * @param string $name The name of the service to return.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function getService($name)
    {
        return $this->container->get($name);
    }

}