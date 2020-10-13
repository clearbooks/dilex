# Dilex

## Rationale

Dilex is an extension of Symfony to provide custom ContainerInterop compatibility and a Silex-like interface. You can specify a custom fallback container, which will be used when an entry cannot be resolved by Symfony's container.

## Usage

```php
$environment = 'dev';
$debug = true;
$container = new DependencyInjectionContainer(); // replace with your real DI initialisation code
$application = new \Clearbooks\Dilex\Dilex( $environment, $debug, $container );
```

Once the application has been instantiated you can create ```Endpoint``` classes to be executed for a given API route. Here's a simple example.

```php
namespace Your\Organisation\Endpoint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Clearbooks\Dilex\Endpoint;

class ExampleEndpoint implements Endpoint
{
    public function execute( Request $request )
    {
        return new JsonResponse( ['hello' => 'world'] );
    }
}

```

While the above example has no constructor the main benefit of Dilex is that the endpoint will be instantiated through your DI container of choice, so you can inject dependencies into the constructor of the Endpoint per your DI container documentation.

You can then associate your ```Endpoint``` implementation with an API route using Dilex's ```get``` method.

```php
use Your\Organisation\Endpoint\ExampleEndpoint;
/*
 * Initialisation code above skipped
 */
$application->get( '/', ExampleEndpoint::class );

```

## Credits

![stamp_small](https://cloud.githubusercontent.com/assets/980959/9278343/27074a4c-42a8-11e5-8262-89c1d6f2217e.png)
