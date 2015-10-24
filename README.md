# Dilex

## Rationale

Dilex is an extension of Silex to provide ContainerInterop compatibility.
While other libraries exist to accomplish similar goals most of them still use Pimple as a service locator which is something Dilex goes to great pains to avoid.

## Differences to Silex


Dilex consists of replacements for Silex's controller & callback resolvers, so affects the usage of ```before``` and endpoint methods such as ```get```.

Instead of being handled by callables ```before``` and ```get``` now require you to pass in a class implementing ```Middleware``` and ```Endpoint``` respectively.

## Usage

To apply the Dilex extensions to a Silex ```Application``` object you use the provided ```ApplicationBuilder``` which requires an ```Application``` and a ```ContainerInterop``` compliant dependency injection container

```php
$application = new \Silex\Application;
$container = new DependencyInjectionContainer(); // replace with your real DI initialisation code
\Clearbooks\Dilex\ApplicationBuilder::build( $container, $application );
```

Once the application has been decorated you can create ```Endpoint``` classes to be executed for a given API route, here's a simple example.

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

You can then associate your ```Endpoint``` implementation with an API route using Silex's ```get``` method.

```php
use Your\Organisation\Endpoint\ExampleEndpoint;
/*
 * Initialisation code above skipped
 */
$application->get( '/', ExampleEndpoint::class );

```



## Credits

![stamp_small](https://cloud.githubusercontent.com/assets/980959/9278343/27074a4c-42a8-11e5-8262-89c1d6f2217e.png)
