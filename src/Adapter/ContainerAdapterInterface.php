<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface ContainerAdapterInterface
{
    /**
     * Check if a service is registered with this name.
     *
     * @param string $name The name of the service to check.
     *
     * @return bool
     *
     * @throws AdapterException
     */
    public function hasService(string $name): bool;

    /**
     * Return a service matching the provided name.
     *
     * @param string $name The name of the service to return.
     *
     * @throws NotFoundException
     * @throws AdapterException
     */
    public function getService(string $name);

    /**
     * Set a new service on the container.
     *
     * @param string $name    The name of the service to set.
     * @param mixed  $service The service to register.
     *
     * @return self
     *
     * @throws AdapterException
     */
    public function setService(string $name, $service): self;

    /**
     * Register a callable factory for the container.
     *
     * @param string   $name    The name of the service to register.
     * @param callable $factory The factory callable responsible for creating the service.
     *
     * @return self
     *
     * @throws AdapterException
     */
    public function setFactory(string $name, callable $factory): self;
}
