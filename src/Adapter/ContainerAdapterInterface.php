<?php

namespace Arp\Container\Adapter;

use Arp\Container\Exception\NotCreatedException;
use Arp\Container\Exception\NotFoundException;
use Arp\Container\Exception\NotRegisteredException;

/**
 * ContainerAdapterInterface
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface ContainerAdapterInterface
{
    /**
     * Check if a service is registered with this name.
     *
     * @param string $name  The name of the service to check.
     *
     * @return bool
     */
    public function hasService($name);

    /**
     * Return a service matching the provided name.
     *
     * @param string $name The name of the service to return.
     *
     * @return mixed
     *
     * @throws NotFoundException
     */
    public function getService($name);

    /**
     * createService
     *
     * Create a new service with the provided name and pass in the $options. This method provides the ability
     * to *always* create a new instance of the requested service.
     *
     * @param string    $name     The name of the service to create.
     * @param array     $options  The service creation options.
     *
     * @return mixed
     *
     * @throws NotCreatedException
     */
    public function createService($name, array $options);

    /**
     * Set a new service on the container.
     *
     * @param string $name     The name of the service to set.
     * @param mixed  $service  The service to register.
     *
     * @return mixed
     *
     * @throws NotRegisteredException
     */
    public function setService($name, $service);

    /**
     * Register a callable factory for the container.
     *
     * @param string   $name     The name of the service to register.
     * @param callable $factory  The factory callable responsible for creating the service.
     *
     * @return mixed
     *
     * @throws NotRegisteredException
     */
    public function setServiceFactory($name, callable $factory);

    /**
     * Register a factory class name
     *
     * @param string    $name              The name of the service to register.
     * @param callable  $factoryClassName  The factory callable responsible for creating the service.
     *
     * @return mixed
     *
     * @throws NotRegisteredException
     */
    public function setServiceFactoryConfig($name, $factoryClassName);

}
