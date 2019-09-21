<?php

namespace Arp\Container\Factory;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * ServiceFactoryInterface
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Factory
 */
interface ServiceFactoryInterface
{
    /**
     * __invoke
     *
     * Create a new service.
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param array              $options
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = []);

}