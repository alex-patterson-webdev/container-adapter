# Arp\Container

## About

A PSR-11 compatible Dependency Injection Container wrapper that supports generic service registration.

## Installation

Installation via [Composer](https://getcomposer.org).

    require alex-patterson-webdev/container ^2
    
## Usage

We can create a PSR-11 compatible container by creating a new instance of `Arp\Container\Container` and providing an concrete implementation of an 
adapter instance.
   
    use Arp\Container\Container;
   
    
   
    $container = new Container($adapter);   
   
There are a number of supporting projects that implement a range of Adapters for different containers. Please see the relevant 
project for container specific documentation.

- `alex-patterson-webdev/container-pimple` Provides an adapter for the Symfony pimple container.
- `alex-patterson-webdev/container-zend-service-manager` Provides an adapter for the Zend Framework Service Manager container.
   
### Get Services   
   
We can now access the container's services via `get($name)`.

    $service = $container->get($name);

### Check if services are registered

And check if services are registered with `has($name)`.

    $container->has('FooService'); // bool
    
### Service Providers

The PSR-11 specification intentionally omits the registration of services. This is because there are already a number of popular containers
that use different strategies. Generally, there are two approaches, using configuration files and runtime registration.  

To provide a generic interface for all containers this module provides the `Arp\Container\Provider\ServiceProviderInterface` interface.
The provider accepts a `Arp\Container\Adapter\ContainerAdapterInterface` which abstracts the service registration for concrete containers. 

    class MyServiceProvider implements ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter)
        {
            // .... use the adapter to register services on the container
            $adapter->setService('FooService', function($container) {
                return new FooService();
            });
            
            $adapter->setService('BarService', function($container) {
                return new BarService();
            });
        }
    }
    
We can then  pass the service provider to the container instance to have the required services registered.

    $container->registerServices(new MyServiceProvider());   

There a three methods of `ContainerAdapterInterface` that provide us the ability to register different types of services

### Created Services

The simplest services to register are objects that are already created.

    $fooService = new FooService();
    $adapter->setService('FooService', $fooService);

When calling `$container->get('FooService')` the container will return the `FooService`.    

### Service Factory

Factories allow a service to registered with the container with a `callable` factory that will resolve the service 
instance when it is first requested via `get()`, this prevents the need to create services that are never used.

Factories can be any PHP `callable`.

    $adapter->setServiceFactory('FooService', function() {
        return new FooService();
    });
    
Or a class implementing an `__invoke()` method.    
    
    class FooServiceFactory
    {
        public function __invoke()
        {
            return new FooService();
        }
    }
    
    $adapter->setServiceFactory('FooService', new FooServiceFactory);
   
When our container creates our service, we can also use it to resolve other dependencies based on configuration options.       
   
    $adapter->setServiceFactory('FooService', function (Container $container) {
        return new FooService(
            $container->get('BarService'),
            $container->get($options['test'])
        );
    });
    
### Service Factory Configuration
        
Using factory class names adds another level of optimization, by delaying the need to create the factory class before it is used. 
This can be useful in applications that have a large number of service factories to register or share.

    $adapter->setServiceFactoryConfig('FooService', 'App\Foo\Factory\FooServiceFactory');
        
### Tests

Unit test cases can be run using PHP unit.

    php vendor/bin/phpunit   
