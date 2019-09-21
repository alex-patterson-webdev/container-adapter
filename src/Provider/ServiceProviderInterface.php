<?php

namespace Arp\Container\Provider;

use Arp\Container\Container;
use Psr\Container\ContainerExceptionInterface;

/**
 * ServiceProviderInterface
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Provider
 */
interface ServiceProviderInterface
{
    /**
     * registerServices
     *
     * Register a collection of services with the container.
     *
     * @param Container $container
     *
     * @throws ContainerExceptionInterface
     */
    public function registerServices(Container $container);

}