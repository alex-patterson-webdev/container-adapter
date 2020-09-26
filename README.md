[![Build Status](https://travis-ci.com/alex-patterson-webdev/container.svg?branch=master)](https://travis-ci.com/alex-patterson-webdev/container)
[![codecov](https://codecov.io/gh/alex-patterson-webdev/container/branch/master/graph/badge.svg)](https://codecov.io/gh/alex-patterson-webdev/container)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-patterson-webdev/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-patterson-webdev/container/?branch=master)

# Arp\Container

## About

A PSR-11 compatible Dependency Injection Container implementation providing agnostic service registration for popular DI containers.

The [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/) provides interoperability 
for PHP projects when _retrieving_ services from different DI container implementations. The `Arp\Container` project is 
intended to complement this specification by unionising the various different approaches that containers use for _service registration_. 
With unification, we can create software libraries which utilise features IoC containers and register services 
in a single location, without dictating a specific container that should be used.

## Installation

This library provides the main `Arp\Container\Container` class and various interfaces that can be used for the container 
registration; it does not however provide a specific implementation. Developers can choose to use their own 'adapters' or use 
and already existing implementation.

Installation via [Composer](https://getcomposer.org).

    require alex-patterson-webdev/container ^1

## Container

The container is an instance of `Arp\Container\Container` and implements `Psr\ContainerInterface`. In addition to the normal
`get()` and `has()` methods required as part of the `Psr\ContainerInterface`, the class provides a `registerServices()` method.
The `registerServices()` method abstracts the service registration by using an implementation of `Arp\Container\ServiceProviderInterface`.

    final class Container implements ContainerInterface
    {    
        public function __construct(ContainerAdapterInterface $adapter)
        public function has($name): bool;
        public function get($name);
        public function registerServices(ServiceProviderInterface $serviceProvider): void;
    }
    
## Adapters
    
Internally the `Container` class proxies its method calls to an adapter class implementing `Arp\Container\Adapter\ContainerAdapterInterface`.
In order to use the container we must provide an adapter class for the container you wish to use.

    use Arp\Container\Container;
    
    $container = new Container($adapter);
    
There are a number of existing projects that have already been created; depending on which container you are currently using.
    
#### [arp\container-array](https://github.com/alex-patterson-webdev/container-array)
- Provides a simple implementation of the required adapters that does not require an existing container. You should use this 
adapter if you do not already have your own container that needs to be integrated with.
    
#### [arp\contianer-pimple](https://github.com/alex-patterson-webdev/container-pimple)
- Required container integration library for the Pimple container
    
#### arp\container-php-di
- Required container integration library for the PHP-DI container. This consists of an adapter that can be used to register 
services with a PHP-DI container you are already using in your projects. (@todo)
    
#### arp\container-laminas-service-manager 
- Required container integration library for the Laminas ServiceManager (@todo)
    
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
    
All adapters provide a way to check and fetch services using `has($name)` and `get($name)`. They also provide a way to 
register different types of services using `setService()` and `setFactory()`.

`setService()` is used when we want to add a value to the container which is returned as it was added. This is useful
for objects that have already been created.

    $fooService = new \stdClass();
    $adapter->setService('FooService', $fooService);
    $adapter->setService('pi', 3.1415);
    
`setFactory()` can be used to register a service factory that will create the service when it is requested from the container.
Any type of PHP `callable` can be used as a service factory. The container is injected as the first argument to all
service factories.

    $adapter->setFactory('BarService', static function (Psr\ContainerInterface $container) {
        return new BarService(
            $container->get('FooService'),
            $container->get('pi')
        );
    };

## Registering Services

We gain access to the required Adapter methods by creating a new `Arp\Container\Provider\ServiceProviderInterface`.

    use Arp\Container\Provider\ServiceProviderInterface;
    use Arp\Container\Adapter\ContainerAdapterInterface;
    
    final class BarServiceProvider implements ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter): void
        {
            $adapter->setFactory('BarService', static function (Psr\ContainerInterface $container) {
                return new BarService(
                    $container->get('FooService'),
                    $container->get('pi')
                );
            };
        }
    }
    
The Service Provider is then passed to the container to register the services.

    $container = new Container($adapter);
    $container->registerServices(new BarServiceProvider());

## Additional Features

There are many additional registration features that popular DI containers provide. In order to support these
differences in a generic way the library provides more specific adapter interfaces that can be implemented.

#### Arp\Container\Adapter\AliasAwareInterface 
Containers which allow services names to be substituted for an alias, or alternative name for the service.

    use Arp\Container\Provider\ServiceProviderInterface;
    use Arp\Container\Adapter\ContainerAdapterInterface;
    use Arp\Container\Provider\Exception\NotSupportedException;

    class FooServiceProvider implments ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter): void
        {
            if (!$adapter instanceof AliasAwareInterface) {
                throw new NotSupportedException('The adapter does not support the use of aliases');
            }
            $adapter->setService('Bar', new \stdClass());
            $adapter->setAlias('Foo', 'Bar');
        }
    }
   
When registering an alias, you must ensure the service being aliased has already been registered.     
   
#### Arp\Container\Adapter\FactoryClassAwareInterface

Allows registration of service factories as strings. This is a useful if most of your service registration is 
configuration based as you will not need to create the factories to register them, they will only be created
once you request the relevant service.

    use Arp\Container\Provider\ServiceProviderInterface;
    use Arp\Container\Adapter\ContainerAdapterInterface;
    use Arp\Container\Provider\Exception\NotSupportedException;

    class BarServiceFactory {
        public function __invoke(ContainerInterface $container): BarService
        {
            return new BarService($container->get('FooService'));
        }
    }

    class FooServiceProvider implments ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter): void
        {
            if (!$adapter instanceof FactoryClassAwareInterface) {
                throw new NotSupportedException('The adapter does not support the use of string factories');
            }
            $adapter->setFactoryClass('Bar', BarServiceFactory::class);
        }
   }

## ConfigServiceProvider

We can reduce the need to write repeated Service Provider logic by using a configuration array passed to the 
`Arp\Provider\ConfigServiceProvider`.
    
    $config = [
        ConfigServiceProvider::FACTORIES = [
            'FooService' => static function (Psr\ContainerInterface $container) {
                return new FooService($container->get('BarService'));
            }
        ],
        ConfigServiceProvider::ALIASES = [
            'FooAlias' => 'FooService',
            'AnotherAliasForBar' => 'BarService',
        ],
        ConfigServiceProvider::SERVICES = [
            'BarService' => new BarService();
        ],
    ];
    $container->registerService(new ConfigServiceProvider($config));

## Unit Tests

Unit test using PHPUnit

    php vendor/bin/phpunit
