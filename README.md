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

The container provides two ways to add services to the container.

#### Defining Services

When we already have our service created, we can add it to the container using `set($name, $service)`.

    $fooService = new FooService();
    $container->set('FooService', $fooService);

#### Defining Factory Services

Factories allow a service to registered with the container with a `callable` factory that will resolve the service 
instance when it is first requested via `get()`, this prevents the need to create services that are never used.

Factories can be any PHP `callable`.

    $container->setFactory('FooService', function() {
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
    $container->setFactory(new FooServiceFactory);
   
When our container creates our service, we can also use it to resolve other dependencies based on configuration options.       
   
    use Arp\Container\Container;
   
    $container->setFactory('FooService', function (Container $container, $name, array $options) {
        return new FooService(
            $container->get('BarService'),
            $container->get($options['test'])
        );
    });
    
Using factory class names adds another level of optiomization, by delaying the need to create the factory class before it is used. 
This can be useful in applications that have a large number of service factories to register or share.

    $container->setFactory('FooService', 'App\Foo\Factory\FooServiceFactory');
    
    
#### Service Providers

Service registration can also be encapsulated into an object implementing `Arp\Container\Provider\ServiceProviderInterface`.
Service providers can be implemented in consuming applications and provide a central location to configure the container.

This package contains a simple `ConfigServiceProvider` allowing service factories to be registered from `array` configuration.

For example 

    $services = [
        'BarService' => new BarService(),
    ];
    
    $factories = [
        'FooService' => MyApp\Factory\FooServiceFactory::class,
    ];

    $configServiceProvider = new ConfigServiceProvider($services, $factories);
    $configServiceProvicer->registerServices($container);
        
### Tests

Unit test cases can be run using PHP unit.

    php vendor/bin/phpunit   
    