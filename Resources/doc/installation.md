Installation
============

## Prerequisites

This bundle requires Symfony >= 2.6 (3.x.x and 4.x.x).

The off-the-shelf version of the bundle is compatible with standard user/password authentication. If your system is not using this, you will have to configure a [security token class](configuration.md) for your authentication methods.

If you're using anything other than Doctrine ORM to manage the User Entity you will have to implement a [persister service](persister.md).

If you're using Symfony 4, you must install templating package :

```bash
php composer.phar require templating
```

and configure it with your favorite engine : 

```yaml
framework:
    templating:
        engines: twig
```

## Installation

### Step 1: Download using Composer

Add this bundle via Composer:

```bash
php composer.phar require scheb/two-factor-bundle
```

When being asked for the version, use the latest stable release or any different version you want.

Alternatively you can also add the bundle directly to composer.json:

```js
{
    "require": {
        "scheb/two-factor-bundle": "~1.0"
    }
}
```

and then tell Composer to install the bundle:

```bash
php composer.phar update scheb/two-factor-bundle
```

### Step 2: Enable the bundle

Enable this bundle in your app/AppKernel.php:

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
    );
}
```

If you're using Symfony >= 4 you have to edit config/bundles.php :

```php
return [
	// ...
    Scheb\TwoFactorBundle\SchebTwoFactorBundle::class => ['all' => true],
];

```


### Step 3: Configure

Next you'll want probably want to [configure the bundle](configuration.md).

For more specific configuration information, see [Google Authenticator](google.md) or [Email](email.md).
