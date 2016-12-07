# How to Use

## Common methods

TOTP and HOTP objects have the following common methods:

* `public function at(int $input)`: generates an OTP at the specified counter
* `public function verify(string $otp, int|null $input, int|null $window)`: verifies if the OTP is valid for the specified input (timestamp, counter...). If no input is set, it will try to use the current timestamp or current counter.
* `public function getSecret()`: returns the secret
* `public function getLabel()`: returns the label
* `public function getIssuer()`: returns the issuer
* `public function setIssuer(string $issuer)`: sets the issuer
* `public function isIssuerIncludedAsParameter()`: if true and if the issuer is set, the issuer is also included into the query parameters
* `public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter)`: defines if the issuer is included in the query parameters (default `true`)
* `public function getDigits()`: returns the number of digits of OTPs
* `public function getDigest()`: returns the digest used to calculate the OTP
* `public function getParameter(string $key)`: returns a custom parameter
* `public function getParameters()`: returns all parameters
* `public function setParameter(string $key, mixed $value)`: sets a custom parameter
* `public function getProvisioningUri()`: returns a provisioning URI to ease integration in applications

### Counter Based OTP (HOTP)

This OTP object has a specific method:

* `public function getCounter()`: returns the current counter

### Time Based OTP (TOTP)

This OTP object has specific methods:

* `public function now()`: return an OTP at the current timestamp
* `public function getPeriod()`: returns the period (in seconds)

## My first OTPs

All OTP objects need at least the following parameters to be set:
* The secret: a base32 encoded secret
* The number of digits: we recommend to use at least 6 digits (default value). More than 10 may be difficult to use by the owner
* The digest: SHA-2 algorithms are recommended (default is `sha1`)

For `TOTP` only:
* A period: in general 30 seconds

For `HOTP` only:
* A counter: we recommend you start at `0`, but you can set any value (at least 0)

Hereafter a simple example using TOTP:

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP();
$totp->now(); // e.g. will return '123456'
$totp->verify('123456'); // Will return true

// At least 30 seconds later:
$totp->verify('123456'); // Will return false
```

And using HOTP:

```php
<?php
use OTPHP\HOTP;

$hotp = new HOTP();
$hotp->at(1000); // e.g. will return '123456'
$hotp->verify('123456', 1000); // Will return true
$hotp->verify('123456', 1000); // Will return false as the current counter is now 1001
```

## The secret

If the secret is not set during the object instantiation, then a 256 bits random secret is set.
Depending on your needs, you can define your own secret. Just pass a secret encoded in Base32 as the second argument.

```php
<?php
use OTPHP\HOTP;

$hotp = new HOTP(
    "alice@google.com", // The label (string or null)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);
```

See [this page](Secret.md) to generate such a secret.

## The window parameter

The method `verify` has a `window` parameter. By default, its value is `null`. This means that the OTP will be tested at the exact counter/timestamp.

### Window and HOTP

If the value is an integer, the method will try all OTP from `counter` to `counter + window`.
For example, if the `counter` is `1000` and the window `10`, the OTP tested are within `1000` and `1010`.

```php
$hotp->verify('123456', 999); // Will return false
$hotp->verify('123456', 999, 10); // Will return true (1000 is tested)
```

### Window and TOTP

The window of timestamps goes from `timestamp - window * period` to `timestamp + window * period`.
For example, if the `window` is `5`, the period `30` and the timestamp `1476822000`, the OTP tested are within `1476821850` (`1476822000 - 5 * 30`) and `1476822150` (`1476822000 + 5 * 30`).

```php
$totp->at(1000); // e.g. will return '123456'
$totp->verify('123456'); // Will return true
// 30 seconds later
$totp->verify('123456'); // Will return false
$totp->verify('123456', null, 1); // Will return true during the next period
```

## Application Configuration

Applications that support OTPs are, in general, able to easily configure an OTP.
This configuration is possible through a provisioning URI that contains all OTP's parameters.
Usually, that provisioning URI is loaded by the application using a QR Code.

This library is able to create provisioning URIs according to the OTP parameters.

You just have to:

- make sure that a label is defined (constructor argument or `setLabel` method)
- call the method `getProvisioningUri`.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP
```

The provisioning URI is used as the QR Code content. Some online services allow you to generate QR Codes that you can integrate into your website.

Hereafter two examples using the Google Chart API:

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com" // The label (string)
);

$google_chart = $totp->getQrCodeUri();
echo "<img src='{$google_chart}'>";
```

If you want to use another QR Code Generator Service, just pass the URI as the first argument of `getQrCodeUri`.
Please note that this URI MUST contain a placeholder for the OTP Provisioning URI. By default this placeholder is `{PROVISIONING_URI}`, but you can change it with the second argument.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com" // The label (string)
);

$goqr_me = $totp->getQrCodeUri(
    'http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=[DATA]&qzone=2&margin=0&size=300x300&ecc=H',
    '[DATA]'
);
echo "<img src='{$goqr_me}'>";
```

### Google Authenticator Example

The library works with the Google Authenticator application for iPhone and Android.

Google only supports SHA-1 digest algorithm, 30 second period and 6 digits OTP. Other values for these parameters are ignored by the Google Authenticator application.

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Company%3Aalice%2540google.com%3Fsecret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Company)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);

echo "Current OTP: " . $totp->now();
```

### Other Applications Example

The following barcode will not work with Google Authenticator because digest algorithm is SHA-512, there are 8 digits and the counter is 10 seconds. But it should work with other applications such as FreeOTP, which support those parameters.

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Company%3Aalice%2540google.com%3Falgorithm%3Dsha512%26digits%3D8%26period%3D10%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Company)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP", // The secret encoded in base 32 (string)
    10,                 // The period (int)
    'sha512',           // The digest algorithm (string)
    8                   // The number of digits (int)
);

echo "Current OTP: " . $totp->now();
```

## Issuer

As the user may have multiple OTP using the same label (e.g. the user email), it is useful to set the issuer parameter to identify the service that provided the OTP.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);
$totp->setIssuer('My Service');
```

By default and [to be compatible with Google Authenticator](https://github.com/google/google-authenticator/wiki/Key-Uri-Format#label), the issuer is set in the query parameters and as the label prefix.

```php
echo $totp->getProvisioningUri(); // Will return otpauth://totp/My%20Service%3Aalice%40google.com?issuer=My%20Service&secret=JBSWY3DPEHPK3PXP
```

If you do not want to get the issuer as a query parameter, you can remove it by using the method `setIssuerIncludedAsParameter(bool)`.

```php
$totp->setIssuerIncludedAsParameter(false);
echo $totp->getProvisioningUri(); // Will return otpauth://totp/My%20Service%3Aalice%40google.com?secret=JBSWY3DPEHPK3PXP
```

## Hash algorithms

You can use any hash algorithm listed by [`hash_algos()`](http://php.net/manual/en/function.hash-algos.php).
Note that most applications only support `md5`, `sha1`, `sha256` and `sha512`.
You must verify that the algorithm you want to use is supported by the application your clients might be using.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP", // The secret encoded in base 32 (string)
    30,                 // The period (int)
    'ripemd160',        // The digest algorithm (string)
    6                   // The number of digits (int)
);

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?digest=ripemd160&secret=JBSWY3DPEHPK3PXP
```

## Custom parameters

OTP objects are able to support custom parameters.
These parameters are available in the provisioning URI or from the method `getParameter`.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);
$totp->setParameter('foo', 'bar');

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar
```

### Image

Some applications such as FreeOTP can load images from an URI (`image` parameter).

> Please note that at the moment, we cannot list applications that support this parameter.

```php
<?php
use OTPHP\TOTP;

$totp = new TOTP(
    "alice@google.com", // The label (string)
    "JBSWY3DPEHPK3PXP"  // The secret encoded in base 32 (string)
);
$totp->setParameter('image', 'https://foo.bar/otp.png');

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&image=https%3A%2F%2Ffoo.bar%2Fotp.png
```

When you load a QR Code using this input data, the application will try to load the image at `https://foo.bar/otp.png`.

## The factory

In some cases, you want to load a provisioning URI and get an OTP object.
That is why we created a factory.

```php
use OTPHP\Factory;

$otp = Factory::loadFromProvisioningUri('otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar');

// The variable $otp is now a valid TOTPInterface of HOTPInterface object with all parameters set (including custom parameters)
```
