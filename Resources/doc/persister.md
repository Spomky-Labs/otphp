Implementing a custom persister
===============================

The bundle assumes that you're using Doctrine to store your user entity. If you're using something else, e.g. your user
entity is managed by an API, you have to implement a custom persister service.

The persister has to implement `Scheb\TwoFactorBundle\Model\PersisterInterface`.

As an example see the `Scheb\TwoFactorBundle\Model\Persister\DoctrinePersister`, which is provided by the bundle.

Register it as a service and configure the service name:

```yaml
scheb_two_factor:
    persister: acme.custom_persister
```
