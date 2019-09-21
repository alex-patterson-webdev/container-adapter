<?php

namespace Arp\Container;

use Arp\Container\Adapter\ContainerAdapterInterface;
use Arp\Container\Exception\ContainerException;
use Arp\Container\Exception\NotCreatedException;
use Arp\Container\Exception\NotFoundException;
use Arp\Container\Factory\ServiceFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Container
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\Container
 */
class Container implements ContainerInterface
{
    /**
     * $adapter
     *
     * @var ContainerAdapterInterface
     */
    protected $adapter;

    /**
     * $factories
     *
     * @var array
     */
    protected $factories = [];

    /**
     * $options
     *
     * @var array
     */
    protected $options = [];

    /**
     * __construct.
     *
     * @param ContainerAdapterInterface $adapter
     */
    public function __construct(ContainerAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * has
     *
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($name)` returning true does not mean that `get($name)` will not throw an exception.
     * It does however mean that `get($name)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $name Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($name)
    {
        return ($this->adapter->hasService($name) || isset($this->factories[$name]));
    }

    /**
     * get
     *
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string  $name  Identifier of the entry to look for.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get($name)
    {
        if ($this->adapter->hasService($name)) {
            return $this->adapter->getService($name);
        }
        elseif (isset($this->factories[$name])) {
            $this->set($name, $this->createService($name));

            return $this->get($name);
        }

        throw new NotFoundException(sprintf(
            'Unable to find a service registered with name \'%s\'.',
            $name
        ));
    }

    /**
     * setService
     *
     * Set a new service on the container.
     *
     * @param string $name     The name of the service to set.
     * @param mixed  $service  The service to register.
     *
     * @throws ContainerExceptionInterface
     */
    public function set($name, $service)
    {
        $this->adapter->setService($name, $service);
    }

    /**
     * createService
     *
     * Create a new service using a registered factory class.
     *
     * @param string  $name     The name of the service to create.
     * @param array   $options  Optional factory creation options.
     *
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws NotCreatedException
     */
    public function createService($name, array $options = [])
    {
        if (! isset($this->factories[$name])) {

            throw new NotCreatedException(sprintf(
                'No service factory can be found for service \'%s\'.',
                $name
            ));
        }

        $factory = $this->factories[$name];

        if (is_string($factory)) {
            $factory = new $factory;
        }

        if (! is_callable($factory)) {

            throw new NotCreatedException(sprintf(
                'The service factory must be an of type \'callable\'; \'%s\' registered for service \'%s\'.',
                (is_object($factory) ? get_class($factory) : gettype($factory)),
                $name
            ));
        }

        try {
            return $factory($this, $name, $options);
        }
        catch(ContainerExceptionInterface $e) {

            throw $e;
        }
        catch (\Throwable $e) {

            throw new NotCreatedException(
                sprintf(
                    'An error occurred while creating service \'%s\' : %s',
                    $name,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * setServiceFactory
     *
     * Set a factory to create a service.
     *
     * @param string    $name     The name of the service to set.
     * @param callable  $factory  The service to register.
     *
     * @throws ContainerExceptionInterface
     */
    public function setFactory($name, $factory)
    {
        if (is_string($factory) && ! is_a($factory, ServiceFactoryInterface::class, true)) {

            throw new ContainerException(sprintf(
                'Invalid factory class \'%s\' provided for service \'%s\'.',
                $factory,
                $name
            ));
        }
        elseif (! is_callable($factory)) {

            throw new ContainerException(sprintf(
                'Factory classes must be of type \'string\' or \'callable\'; \'%s\' provided for service \'%s\'.',
                gettype($factory),
                $name
            ));
        }

        $this->factories[$name] = $factory;
    }

}