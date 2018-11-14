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

This library needs at least `PHP 7.1`.
It has been successfully tested using `PHP 7.1`, `PHP 7.2` and nightly branch.

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

$otp = TOTP::create();
echo 'The current OTP is: '.$otp->now();
```

In the example above, we use the `TOTP` class, but you can use the `HOTP` one the same way.

Then, you have to configure you applications. 
You can use the provisioning Uri (`$otp->getProvisioningUri();`) as QR Code input to easily configure all of them.

We recommend you to use your own QR Code generator (e.g. [BaconQrCode](https://packagist.org/packages/bacon/bacon-qr-code)).
If you do not have your own generator, the classes provide a convenient way to get an Uri to the Google Chart API which will generate it for you:

```php
$googleChartUri = $totp->getQrCodeUri();
echo "<img src='{$googleChartUri}'>";
```

Now that your applications are configured, you can verify the generated OTPs:

```php
$otp->verify($input); // Returns true if the input is verified, otherwise false.
```

# Advanced Features

* [Customization](Customize.md)
* [Application Configuration](AppConfig.md): get the provisioning Uri
* [Factory](Factory.md): from a provisioning Uri to an OTP object
* [Window](Window.md): the window parameter
* [Q&A](QA.md): Questions and Answers

# Upgrade

* [From `v8.x` to `v9.x`](UPGRADE_v8-v9.md)

## Base 32 Encoder

Please note that the internal Base32 encoder changed on versions `8.3.2` and `9.0.2`.

**Before**

```
use Base32\Base32;

$encoded = Base32::encode('foo');
```
**After**

```
use ParagonIE\ConstantTime\Base32;

$encoded = Base32::encode('foo');
```
