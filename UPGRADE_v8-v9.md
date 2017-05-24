# Upgrade from `v8.x` to `v9.x`

The upgrade from `v8.x` to `v9.x` is quite easy as only the construction method is modified

* All classes are now private.
* Minimal PHP version: `7.1`.
* `HHVM` is not supported nor tested anymore.
* Strict type hinting for all methods (including protected and private)
* Polyfills removed.

## Object Instantiation

Constructors are now private. You have to use provided static methods to create an object.

**Before**

```php
<?php

us OTPHP\TOTP;

$otp = new TOTP();
```

**After**

```php
<?php

us OTPHP\TOTP;

$otp = TOTP::create();
```

## Parameter order

The label is not passed to the object construction method anymore.
Please use its dedicated method.

**Before**

```php
<?php

us OTPHP\TOTP;

$otp = new TOTP($label);
```

**After**

```php
<?php

us OTPHP\TOTP;

$otp = TOTP::create();
$otp->setLabel($label);
```
