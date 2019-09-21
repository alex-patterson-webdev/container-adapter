<?php

namespace Arp\Container\Adapter;

use Psr\Container\ContainerExceptionInterface;

/**
 * ContainerAdapterInterface
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface ContainerAdapterInterface
{
    /**
     * hasService
     *
     * Check if a service is registered with this name.
     *
     * @param string $name  The name of the service to check.
     *
     * @return bool
     */
    public function hasService($name);

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
    public function getService($name);

    /**
     * setService
     *
     * Set a new service on the container.
     *
     * @param string $name     The name of the service to set.
     * @param mixed  $service  The service to register.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function setService($name, $service);

    /**
     * createService
     *
     * Create a new service with the provided name and pass in the $options.
     *
     * @param string    $name     The name of the service to create.
     * @param callable  $factory  The factory used to create the service.
     * @param array     $options  The service creation options.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function createService($name, callable $factory, array $options);
}