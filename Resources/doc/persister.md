Implement a custom persister service
====================================

The bundle assumes that you're using Doctrine to store your user entity. If you're using something else,
e.g. your user entity is managed by an API, you have to implement a custom persister service.

The persister has to implement `Scheb\TwoFactorBundle\Model\PersisterInterface`.

As an example see the `Scheb\TwoFactorBundle\Model\DoctrinePersister`, which is provided with this bundle.

Register it as a service and configure the service name:

```yaml
scheb_two_factor:
    persister: acme_demo.custom_persister
```
