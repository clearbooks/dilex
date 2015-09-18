# Dilex

## Rationale

Dilex is an extension of Silex to provide ContainerInterop compatibility.
While other libraries exist to accomplish similar goals most of them still use Pimple as a service locator which is something Dilex goes to great pains to avoid.

## Differences to Silex


Dilex consists of replacements for Silex's controller & callback resolvers, so affects the usage of ```before``` and endpoint methods such as ```get```.

Instead of being handled by callables ```before``` and ```get``` now require you to pass in a class implementing ```Middleware``` and ```Endpoint``` respectively.

## Credits

![stamp_small](https://cloud.githubusercontent.com/assets/980959/9278343/27074a4c-42a8-11e5-8262-89c1d6f2217e.png)
