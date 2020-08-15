<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
interface AliasAwareInterface extends ContainerAdapterInterface
{
    /**
     * Set an alias for a given service
     *
     * @param string $alias The name of the alias to set
     * @param string $name  The name of the service that
     *
     * @return ContainerAdapterInterface
     *
     * @throws  AdapterException If the alias cannot be set
     */
    public function setAlias(string $alias, string $name): ContainerAdapterInterface;
}
