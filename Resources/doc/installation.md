Installation
============

## Prerequisites

This bundle requires Symfony 2.1+.

You have to use a user/password based security mechanism.

Your user entity has to be managed by Doctrine ORM.


## Installation

### Step 1: Download using Composer



Add this bundle via Composer:

```bash
php composer.phar require scheb/two-factor-bundle
```

When being asked for the version use dev-master or any different version you want.

Alternatively you can also add the bundle directly to composer.json:

```js
{
    "require": {
        "scheb/two-factor-bundle": "dev-master"
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
<?php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
    );
}
```
