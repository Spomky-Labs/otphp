# How to Use

## Common methods

TOTP and HOTP objects have the following common methods:

* `public function at($input)`: generates an OTP at the specified counter
* `public function verify($otp, $input, $window)`: verifies if the OTP is valid for the specified input (timestamp, counter...)
* `public function getSecret()`: returns the secret
* `public function getLabel()`: returns the label
* `public function getIssuer()`: returns the issuer
* `public function setIssuer($issuer)`: sets the issuer
* `public function isIssuerIncludedAsParameter()`: if true, the issuer is also included into the query parameters
* `public function setIssuerIncludedAsParameter($issuer_included_as_parameter)`: defines if the issuer is added into the query parameters
* `public function getDigits()`: returns the number of digits of OTPs
* `public function getDigest()`: returns the digest used to calculate the OTP
* `public function getParameter($parameter)`: returns a custom parameter
* `public function getParameters()`: returns all parameters
* `public function setParameter($parameter, $value)`: sets a custom parameter
* `public function getProvisioningUri()`: returns a provisioning URI to ease integration in applications

### Counter Based OTP (HOTP)

This OTP object has a specific method:

* `public function getCounter()`: returns the current counter

### Time Based OTP (TOTP)

This OTP object has a specific method:

* `public function now()`: return an OTP at the current timestamp
* `public function getPeriod()`: returns the period (in seconds)

## My first OTPs

All OTP objects need at least the following parameters to be set:
* The label: for example the name of the owner, an email address
* The secret: a base32 encoded secret. See [this page](Secret.md) to generate such secret
* The number of digits: we recommend to use at least 6 digits (default value). More than 10 may be difficult to use by the owner
* The digest: Sha-2 algorithms are recommended (default is `sha1`).

For `TOTP` only:
* A period: in general 30 seconds

For `HOTP` only:
* A counter: we recommend you to start at `0`, but you can set any value (at least 0).

Hereafter an example using TOTP:

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
    30,                 // The period (default value is 30)
    'sha1',             // The digest algorithm (default value is 'sha1')
    6                   // The number of digits (default value is 6)
);

$totp->now(); //e.g. will return '123456'
$totp->verify('123456'); //Will return true.

// At least 30 seconds later:
$totp->verify('123456'); //Will return false.
```

And using HOTP:

```php
<?php
use OTPHP\HOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
    1000,               // The counter (default value is 0)
    'sha1',             // The digest algorithm (default value is 'sha1')
    6                   // The number of digits (default value is 6)
);

$hotp->at(1000); //e.g. will return '123456'
$hotp->verify('123456', 1000); //Will return true.
$hotp->verify('123456', 1000); //Will return false as the current counter is now 1001.
```

## The window parameter

The method `verify` has a `windows` parameter. By default, it value is `null`. It means that the OTP will be tested at the exact counter/timestamp.

### Window and HOTP

If the value is an integer, the method will try all OTP from `counter` to `counter+window`.
For example, if the `counter`is `1000` and the window `10`, the OTP tested are within `1000` and `1010`.

```php
$hotp->verify('123456', 999); //Will return false
$hotp->verify('123456', 999, 10); //Will return true (1000 is tested)
```

### Window and TOTP

The window of timestamps goes from `- $window * period + timestamp` to `+ $window * period + timestamp`.
For example, if the `window`is `5`, the period `30` and the timestamp `600`, the OTP tested are within `450` and `750`.

```php
$totp->at(1000); //e.g. will return '123456'
$totp->verify('123456'); //Will return true.
// 30 seconds later
$totp->verify('123456'); //Will return false
$totp->verify('123456', null, 1); //Will return true during the next period
```

## Google Authenticator Compatible

The library works with the Google Authenticator iPhone and Android app, and also
includes the ability to generate provisioning URI's for use with the QR Code scanner
built into the app.

Google only supports SHA-1 digest algorithm, 30 second period and 6 digits OTP. Other values for these parameters are ignored by the Google Authenticator application.

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
);

$totp->getProvisioningUri(); // => 'otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP'
```

You can now create a QRCode using the provisioning URI as input data.

### Valid example

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Fsecret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
);

echo "Current OTP: ". $totp->now();
```

## Not Compatible with Google Authenticator

The following barcode will not work with Google Authenticator because digest algoritm is not SHA-1, there are 8 digits and counter is not 30 seconds.

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Falgorithm%3Dsha512%26digits%3D8%26period%3D10%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
    10,                 // The period
    'sha512',           // The digest algorithm
    8                   // The number of digits
);

echo "Current OTP: ". $totp->now();
```

## Hash algorithms

You can use any hash algorithm listed by [`hash_algos()`](http://php.net/manual/en/function.hash-algos.php).
Note that most of applications only support `md5`, `sha1`, `sha256` and `sha512`.
You must verify that the algorithm you want to use can supported by application your client might use.

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
    30,                 // The period
    'ripemd160',        // The digest algorithm
    6                   // The number of digits
);

$totp->getProvisioningUri(); // => 'otpauth://totp/alice%40google.com?digest=ripemd160&secret=JBSWY3DPEHPK3PXP'
```

## Custom parameters

OTP objects are able to support custom parameters.
These parameters are available in the provisioning URI or from the method `getParameter`.

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
);
$totp->setParameter('foo', 'bar');

$totp->getProvisioningUri(); // => 'otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar'
```

### Image

Some applications such as FreeOTP can load images from an URI (`image` parameter).

> Please note that at the moment, we cannot list applications that support this parameter.

```php
<?php
use OTPHP\TOTP;

$totp = new HOTP(
    "alice@google.com", // The label
    "JBSWY3DPEHPK3PXP", // The secret
);
$totp->setImage('https://foo.bar/otp.png');

$totp->getProvisioningUri(); // => 'otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&image=https%3A%2F%2Ffoo.bar%2Fotp.png'
```

When you load a QRCode using this input data, the application will try to load the image at `https://foo.bar/otp.png`.

## The factory

In some cases, you want to load a provisioning URI and get on OTP object.
That is why we created a factory.

```php
use OTPHP\Factory;

$otp = Factory::loadFromProvisioningUri('otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar');

// The variable $otp is now a valid TOTPInterface of HOTPInterface object with all parameters set (including custom parameters)
```
