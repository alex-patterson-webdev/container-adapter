<?php

namespace Arp\Container\Provider;

use Arp\Container\Adapter\ContainerAdapterInterface;
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
     * @param ContainerAdapterInterface $adapter
     *
     * @throws ContainerExceptionInterface
     */
    public function registerServices(ContainerAdapterInterface $adapter);
}
