#Arp\Container

## About

A PSR-11 based dependency injection component that provides a generic interface for service resolution and registration.

## Installation

Installation via [Composer](https://getcomposer.org).

    require alex-patterson-webdev/container ^1
    
## Usage

The container implementation `Arp\Container\Container` requires an adapter to function; The module ships with the following implementations.

- `Arp\Container\Adapter\PimpleContainer` adapter for the [Pimple\Container](https://pimple.symfony.com/).
- `Arp\Container\Adapter\ZendServiceManager` adapter for the [Zend\ServiceManager\ServiceManager](https://github.com/zendframework/zend-servicemanager/)

Assuming we are using Pimple, we can create our container in the following manor.
    
    use Arp\Container\Container;
    use Arp\Container\Adapter\Pimple;
    
    $pimpleContainer = new \Pimple\Container;
    $pimpleAdapter   = new Pimple($pimpleContainer);
    
    $container = new Container($pimpleAdapter);
   
#### Get Services   
   
We can now access the container's services via `get($name)`.

    $service = $container->get($name);

#### Check if services are registered

And check if services are registered with `has($name)`.

    $container->has('FooService') // bool
    
# Service Providers

The PSR-11 specification intentionally omits the registration of services. This is because there are already a number of popular containers
that use different strategies. Generally, there are two approaches, using configuration files and runtime registration.  

To provide a generic interface for all containers this module provides the `Arp\Container\Provider\ServiceProviderInterface` interface.
The provider accepts a `ContainerAdapterInterface` which abstracts the service registration for concrete containers. 

    class MyServiceProvider implements ServiceProviderInterface
    {
        public function registerServices(ContainerAdapterInterface $adapter)
        {
            // .... use the adapter to register services on the container
        }
    }
    
We can the pass the service provider to the container instance.

    $provider = new MyServiceProvider();
    $container->registerServices($provider);   

There a three methods of `ContainerAdapterInterface` that provide us the ability to register different types of services

### Registering services

The simplest services to register are objects that are already created.

    $fooService = new FooService();
    $adapter->setService('FooService', $fooService);

When calling `$container->get('FooService')` the container will return the `FooService`.    

### Registering a Service Factory

Factories allow a service to registered with the container with a `callable` factory that will resolve the service 
instance when it is first requested via `get()`, this prevents the need to create services that are never used.

Factories can be any PHP `callable`.

    $adapter->setFactory('FooService', function() {
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
    
    $adapter->setServiceFactory(new FooServiceFactory);
   
When our container creates our service, we can also use it to resolve other dependencies based on configuration options.       
   
    $adapter->setFactory('FooService', function (Container $container) {
        return new FooService(
            $container->get('BarService'),
            $container->get($options['test'])
        );
    });
    
### Registering a Service Factory class        
        
Using factory class names adds another level of optimization, by delaying the need to create the factory class before it is used. 
This can be useful in applications that have a large number of service factories to register or share.

    $container->setFactory('FooService', 'App\Foo\Factory\FooServiceFactory');
        
### Tests

Unit test cases can be run using PHP unit.

    php vendor/bin/phpunit   
    