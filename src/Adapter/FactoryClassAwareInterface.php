<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface FactoryClassAwareInterface
{
    /**
     * Set the class name of a factory that will create service $name.
     *
     * @param string $name          The name of the service to set the factory for.
     * @param string $factoryClass  The fully qualified class name of the factory.
     *
     * @return ContainerAdapterInterface
     *
     * @throws AdapterException If the factory class cannot be set
     */
    public function setFactoryClass(string $name, string $factoryClass): ContainerAdapterInterface;
}
