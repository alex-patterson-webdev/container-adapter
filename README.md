[![Build Status](https://travis-ci.com/alex-patterson-webdev/container.svg?branch=master)](https://travis-ci.com/alex-patterson-webdev/container)
[![codecov](https://codecov.io/gh/alex-patterson-webdev/container/branch/master/graph/badge.svg)](https://codecov.io/gh/alex-patterson-webdev/container)

# Arp\Container

## About

A PSR-11 compatible Dependency Injection Container implementation which additionally supports generic service registration methods for popular DI containers.

## Installation

Installation via [Composer](https://getcomposer.org).

    require alex-patterson-webdev/container ^1

## Container

The container is an instance of `Arp\Container\Container` and is the PSR-11 compatible, implementing `Psr\ContainerInterface`.
Internally the `Container` class proxies its method calls to an adapter class implementing `Arp\Container\Adapter\ContainerAdapterInterface`.

    use Arp\Container\Container;
    $container = new Container($adapter);    

## Adapters    
   
The container 'adapters' unify the different approaches many popular dependency injection containers take when implementing 
service registration.

The adapter interface exposes a number of basic methods that are common for popular implementations.
    
    namespace Arp\Container\Adpater;

    interface ContainerAdapterInterface
    {
        public function hasService(string $name): bool;
        public function getService(string $name);
        public function setService(string $name, $service);
        public function setFactory(string $name, callable $factory);
    }

A concrete adapter class can implement their own logic in the `setService()` and `setFactory()` to allow the service registration 
methods to differ between different DI container implementations. The basic methods include :

#### Registering a service factory

Service factories are any PHP `callable` type. When retrieving services from the container the callable will be invoked, and the container will be injected.

Factories can be any PHP `callable`.

    use Psr\Container\ContainerInterface;
    
    $adapter->setServiceFactory('FooService', static function(ContainerInterface $container) {
        return new FooService();
    });

#### Registering an existing service

We can also directly set a value using `setService()`, which can be used for services which are already created or that do not
require a factory.

    $adapter->setService('FooService', new FooService());
    $adapter->setService('SpecialNumber', 12345);
    
    $container = new Container($adapter);
    
    $fooService = $container->get('FooService'); // FooService
    $specialNumber = $container->getService('FooService'); // 12345

#### Other features

Some containers support additional service registration features, in these cases there are additional adapter interfaces.

- `Arp\Container\Adapter\FactoryClassAwareInterface` allows registration of service factories as strings. 
This is a useful if most of your service registration is configuration based as you will not need to create the factories to register them. Improving performance.
- `Arp\Container\Adapter\BuildAwareInterface` allows new instances of registered services to be created at runtime, ensuring each call will return a newly created instance.
- `Arp\Container\Adapter\AliasAwareInterface` allows registration of service aliases.

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

    $container->registerServices(new MyServiceProvider());    

## Unit Tests

Unit test using PHPUnit 8

    php vendor/bin/phpunit
