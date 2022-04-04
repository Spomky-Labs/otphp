Documentation
=============

# The Release Process

The release process [is described here](Release.md).

# Maintained Versions

Please note that only version 9 and 10 are maintained.

* New features will be included in 10, and if possible, in version 9.
* Security fixes are incorporated on both releases.

Older versions are not maintained anymore as they need PHP versions that reached End Of Life.  

Support for the version 9 will end when PHP 7.1 will not be maintained (1 Dec 2019).

# Prerequisites

This library needs at least `PHP 7.1` for v9.0 and `PHP 7.2` for v10.0.
It has been successfully tested using `PHP 7.1`, `PHP 7.2`, `PHP 7.3`.

Nightly branch (`PHP 8.0`) fails as dependencies are not yet compatible.

# Installation

The preferred way to install this library is to rely on Composer:

```sh
composer require spomky-labs/otphp
```

By default, documentation and test environment are excluded.
If you want to test the library or get the documentation, please add `--prefer-source` option:

```sh
composer require spomky-labs/otphp --prefer-source
```

# TOTP or HOTP?

This library supports both `TOTP` and `HOTP`.

`TOTP` is a time based one-time password. It lives only for a few seconds (the `period`).
You just have to be sure that the clock of your server and your device are synchronized.
__This is the most common OTP__.

`HOTP` is a counter based one-time password. Every time a password is used, the counter is updated.
You have to verify that the server and the device are synchronized.

# How to use

To create an OTP object, just use the static `create` method. Your object will be able to generate passwords:

```php
<?php
use OTPHP\TOTP;

// A random secret will be generated from this.
// You should store the secret with the user for verification.
$otp = TOTP::create();
echo "The OTP secret is: {$otp->getSecret()}\n";

// Note: use your own way to load the user secret.
// The function "load_user_secret" is simply a placeholder.
$secret = load_user_secret();
$otp = TOTP::create($secret);
echo "The current OTP is: {$otp->now()}\n";
```

In the example above, we use the `TOTP` class, but you can use the `HOTP` one the same way.

Then, you have to configure you applications. 
You can use the provisioning Uri (`$otp->getProvisioningUri();`) as QR Code input to easily configure all of them.

We recommend you to use your own QR Code generator (e.g. [BaconQrCode](https://packagist.org/packages/bacon/bacon-qr-code) or [endroid/qr-code](https://github.com/endroid/qr-code)).

```php
<?php

// Note: You must set label before generating the QR code
$otp->setLabel('Label of your web');
$grCodeUri = $otp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
    '[DATA]'
);
echo "<img src='{$grCodeUri}'>";
```

Now that your applications are configured, you can verify the generated OTPs:

```php
$otp = TOTP::create($secret); // create TOTP object from the secret.
$otp->verify($input); // Returns true if the input is verified, otherwise false.
```

# Advanced Features

* [Customization](Customize.md)
* [Application Configuration](AppConfig.md): get the provisioning Uri
* [Factory](Factory.md): from a provisioning Uri to an OTP object
* [Window](Window.md): the window parameter
* [Preventing TOTP Token Reuse](PreventTokenReuse.md) how to prevent token reuse
* [Q&A](QA.md): Questions and Answers

# Upgrade

* [From `v8.3` to `v9.x`](UPGRADE_v8-v9.md)
* [From `v9.x` to `v10.x`](UPGRADE_v9-v10.md)
