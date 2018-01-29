# TOTP / HOTP library in PHP #

Help me out for a couple of :beers:!

[![Beerpay](https://beerpay.io/Spomky-Labs/otphp/badge.svg?style=beer-square)](https://beerpay.io/Spomky-Labs/otphp)  [![Beerpay](https://beerpay.io/Spomky-Labs/otphp/make-wish.svg?style=flat-square)](https://beerpay.io/Spomky-Labs/otphp?focus=wish)

----

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/?branch=master)
[![Coverage Status](https://coveralls.io/repos/Spomky-Labs/otphp/badge.svg?branch=master&service=github)](https://coveralls.io/github/Spomky-Labs/otphp?branch=master)
[![Dependency Status](https://beta.gemnasium.com/badges/github.com/Spomky-Labs/otphp.svg)](https://beta.gemnasium.com/projects/github.com/Spomky-Labs/otphp)

[![Build Status](https://travis-ci.org/Spomky-Labs/otphp.svg?branch=master)](https://travis-ci.org/Spomky-Labs/otphp)
[![PHP 7 ready](http://php7ready.timesplinter.ch/Spomky-Labs/otphp/badge.svg)](https://travis-ci.org/Spomky-Labs/otphp)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/49e5925d-0dd8-4b89-a215-5eb33b4d96d9/big.png)](https://insight.sensiolabs.com/projects/49e5925d-0dd8-4b89-a215-5eb33b4d96d9)

[![Latest Stable Version](https://poser.pugx.org/spomky-labs/otphp/v/stable.png)](https://packagist.org/packages/spomky-labs/otphp) [![Total Downloads](https://poser.pugx.org/spomky-labs/otphp/downloads.png)](https://packagist.org/packages/spomky-labs/otphp) [![Latest Unstable Version](https://poser.pugx.org/spomky-labs/otphp/v/unstable.png)](https://packagist.org/packages/spomky-labs/otphp) [![License](https://poser.pugx.org/spomky-labs/otphp/license.png)](https://packagist.org/packages/spomky-labs/otphp)

A php library for generating one-time passwords according to [RFC 4226](http://tools.ietf.org/html/rfc4226) (HOTP Algorithm) and [RFC 6238](http://tools.ietf.org/html/rfc6238) (TOTP Algorithm)

This library is compatible with Google Authenticator apps available for Android and iPhone.
It is also compatible with other applications such as [FreeOTP](https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp) for example.

## The Release Process

The release process [is described here](doc/Release.md).

## Prerequisites

This library needs at least `PHP 7.1`.
It has been successfully tested using `PHP 7.1` and nightly branch.

For older PHP versions support, please use release [`8.3.x`](https://github.com/Spomky-Labs/otphp/tree/v8.3.0) of this library.

## Installation

The preferred way to install this library is to rely on Composer:

```sh
composer require spomky-labs/otphp
```

By default, documentation and test environment are excluded.
If you want to test the library or get the documentation, please add `--prefer-source` option:

```sh
composer require spomky-labs/otphp --prefer-source
```

## TOTP or HOTP?

This library supports both `TOTP` and `HOTP`.

`TOTP` is a time based one-time password. It lives only for a few seconds (the `period`).
You just have to be sure that the clock of your server and your device are synchronized.
__This is the most common OTP__.

`HOTP` is a counter based one-time password. Every time a password is used, the counter is updated.
You have to verify that the server and the device are synchronized.

## How to use

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

## Advanced Features

* [Customization](doc/Customize.md)
* [Application Configuration](doc/AppConfig.md): get the provisioning Uri
* [Factory](doc/Factory.md): from a provisioning Uri to an OTP object
* [Window](doc/Window.md): the window parameter
* [Q&A](doc/QA.md): Questions and Answers

## Upgrade

* [From `v8.x` to `v9.x`](UPGRADE_v8-v9.md)

### Base 32 Encoder

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

## Contributing

Requests for new features, bug fixed and all other ideas to make this project useful are welcome.

Please report all issues in [the repository bug tracker](hhttps://github.com/Spomky-Labs/otphp/issues).

Also make sure to [follow these best practices](.github/CONTRIBUTING.md).

## Security Issues

If you discover a security vulnerability within the project, please don't use the bug tracker and don't publish it publicly.
Instead, please contact me at https://gitter.im/Spomky/

## Licence

This software is release under the [MIT licence](LICENSE).
