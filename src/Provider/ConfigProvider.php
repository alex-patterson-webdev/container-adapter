<?php

namespace Arp\Container\Provider;

use Arp\Container\Container;
use Psr\Container\ContainerExceptionInterface;

/**
 * ConfigProvider
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Provider
 */
class ConfigProvider implements ServiceProviderInterface
{
    /**
     * $services
     *
     * @var array
     */
    protected $services = [];

    /**
     * $factories
     *
     * @var array
     */
    protected $factories = [];

    /**
     * __construct.
     *
     * @param array $services
     * @param array $factories
     */
    public function __construct(array $services = [], array $factories = [])
    {
        $this->services  = $services;
        $this->factories = $factories;
    }

    /**
     * registerServices
     *
     * Register a collection of services/factories with the container.
     *
     * @param Container $container  The dependency container.
     *
     * @throws ContainerExceptionInterface
     */
    public function registerServices(Container $container)
    {
        foreach($this->services as $name => $service) {
            $container->set($name, $service);
        }

        foreach ($this->factories as $name => $factory) {
            $container->setFactory($name, $factory);
        }
    }

}