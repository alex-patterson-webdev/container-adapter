[![Build Status](https://travis-ci.com/alex-patterson-webdev/container.svg?branch=master)](https://travis-ci.com/alex-patterson-webdev/container)
[![codecov](https://codecov.io/gh/alex-patterson-webdev/container/branch/master/graph/badge.svg)](https://codecov.io/gh/alex-patterson-webdev/container)

# Arp\Container

## About

A PSR-11 compatible Dependency Injection Container implementation, supporting generic service registration for popular DI containers.

## Installation

Installation via [Composer](https://getcomposer.org).

    require alex-patterson-webdev/container ^1
    
### Implementations

There are a number of projects that implement the interfaces provided in this library. Please see the relevant project for 
container specific documentation.

- [alex-patterson-webdev/container-array](https://github.com/alex-patterson-webdev/container-array)
Adapter for a simple array based container that stores services and factories in PHP arrays

- (In DEV) [alex-patterson-webdev/container-pimple](https://github.com/alex-patterson-webdev/container-pimple)
Adapter for the Symfony Pimple container.

- (In DEV) alex-patterson-webdev/container-laminas : Adapter for the Laminas Service Manager
Adapter for the Laminas Service Manager (Formally Zend 3).

## The Container

The PSR-11 compatible container is `Arp\Container\Container`. Internally the `Container` class proxies its method calls 
to an adapter class implementing `Arp\Container\Adapter\ContainerAdapterInterface`.

To create the container, the library provides a default factory class, `Arp\Container\Factory\ContainerFactory`.
 
    use Arp\Container\Container;
    use Arp\Container\Factory\ContainerFactory;
   
    $config = [
        'adapter' => new \My\Container\Adapter\Foo(),
    ];
    $container = (new ContainerFactory())->create($config);
    
    if ($container->has('Foo')) {
        $fooService = $container->get('Foo');
    }    
    
## Container Adapters    
   
Container Adapters unify the different approaches many popular dependency injection containers take when implementing 
service registration.

The adapter interface exposes a number of basic methods that they all share
    
    namespace Arp\Container\Adpater;

    interface ContainerAdapterInterface
    {
        public function hasService(string $name): bool;
        public function getService(string $name);
        public function setService(string $name, $service): self;
        public function setFactory(string $name, callable $factory): self;
    }

A concrete adapter class can implement their own logic in the  `setService()` and `setFactory()` to allow the service registration 
methods to differ between different DI container implementations. The basic methods include :

#### Registering a service factory

Service factories are any PHP `callable` type. When retrieving services from the container the callable will be invoked 
and the container will be injected.

Factories can be any PHP `callable`.

    use Psr\Container\ContainerInterface;
    
    $adapter->setServiceFactory('FooService', static function(ContainerInterface $container) {
        return new FooService();
    });

#### Registering an existing service

We can also directly set a value using `setService()`, which can be used for service that are already created or that do not
require a factory.

    $adapter->setService('FooService', new FooService());
    $adapter->setService('SpecialNumber', 12345);
    
    $fooService = $adapter->get('FooService');
    $specialNumber = $adapter->getService('FooService');

#### Other features

Some containers support more service registration features than others, in these cases there are additional adapter interfaces.

- `Arp\Container\Adapter\FactoryClassAwareInterface`

Containers that allow registration of service factory classes

- `Arp\Container\Adapter\BuildAwareInterface`

Containers that allow new instances to be created based on passed configuration options
   
## Service Providers

A service provider is any class implementing `Arp\Container\Provider\ServiceProviderInterface`. The interface provides a single place
to interact with the container adapter directly to register services.

    use Arp\Container\Provider\ServiceProviderInterface;
    use Arp\Container\Adapter\ContainerAdapterInterface;
    
    class MyServiceProvider implements ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter)
        {
            $adapter->setService('Hello', new \stdClass());

            $adapter->setFactory('BarService', function($container) {
                return new BarService();
            });
        }
    }
    
The registration of the service We can then pass the service provider to the container and fetch our services.

    // @var \Arp\Container\Container $container
    $container->registerServices(new MyServiceProvider());    

## Unit Tests

Unit test using PHPUnit 8

    php vendor/bin/phpunit
