<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
abstract class AbstractPsrBridgeAdapter implements ContainerAdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Check if a service is registered with this name.
     *
     * @param string $name The name of the service to check.
     *
     * @return bool
     *
     * @throws AdapterException If the container raises an error
     */
    public function hasService(string $name): bool
    {
        try {
            return $this->container->has($name);
        } catch (\Throwable $e) {
            throw new AdapterException(
                sprintf('The check for service \'%s\' failed : %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Return a service matching the provided name.
     *
     * @param string $name The name of the service to return.
     *
     * @return mixed
     *
     * @throws NotFoundException If the requested service cannot be found registered with the container
     * @throws AdapterException If the requested service was found by could not be created or returned
     */
    public function getService(string $name)
    {
        try {
            return $this->container->get($name);
        } catch (NotFoundExceptionInterface $e) {
            throw new NotFoundException(
                sprintf('The service \'%s\' could not be found : %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        } catch (ContainerExceptionInterface $e) {
            throw new AdapterException(
                sprintf('The service \'%s\' was found but could not be returned : %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
