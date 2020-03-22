<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface BuildAwareInterface extends ContainerAdapterInterface
{
    /**
     * Create a new instance of requested service
     *
     * @param string $name    The name of the service to create
     * @param array  $options The service's creation options
     *
     * @return mixed
     *
     * @throws AdapterException If the service cannot be created
     */
    public function build(string $name, array $options = []);
}
