<?php

declare(strict_types=1);

namespace Arp\Container\Adapter;

use Arp\Container\Adapter\Exception\AdapterException;
use Arp\Container\Adapter\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * This class can be used base class for adapters proxying method calls to a PSR Container.
 * Extending classes must implement the remaining requirements of ContainerAdapterInterface.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container\Adapter
 */
abstract class AbstractPsrAdapter implements ContainerAdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
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
        } catch (ContainerExceptionInterface $e) {
            throw new AdapterException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $name The name of the service to return
     *
     * @return mixed
     *
     * @throws NotFoundException
     * @throws AdapterException
     */
    public function getService(string $name)
    {
        try {
            return $this->container->get($name);
        } catch (NotFoundExceptionInterface $e) {
            throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
        } catch (ContainerExceptionInterface $e) {
            throw new AdapterException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
