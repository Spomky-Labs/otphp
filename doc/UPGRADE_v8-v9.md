# Upgrade from `v8.x` to `v9.x`

The upgrade from `v8.x` to `v9.x` is quite easy as only the construction methods are modified.

Also, you have to consider the following changes:
* All classes are now private.
* The minimal PHP version is `7.1`.
* Strict type hinting for all methods (including protected and private methods).
* `HHVM` is not tested anymore.

## Object Instantiation

Constructors are now private. You have to use the provided static methods to create an object.

**Before**

```php
<?php

use OTPHP\TOTP;

$otp = new TOTP();
```

**After**

```php
<?php

use OTPHP\TOTP;

$otp = TOTP::create();
```

## OTP and Label

The label is not passed to the object construction method anymore (was the first parameter).
Please use its dedicated method.

Other parameters are unchanged.

**Before**

```php
<?php

use OTPHP\TOTP;

$otp = new TOTP($label);
```

**After**

```php
<?php

use OTPHP\TOTP;

$otp = TOTP::create();
$otp->setLabel($label);
```
