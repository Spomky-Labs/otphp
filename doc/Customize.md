# OTP Customization

To generate one-time passwords, each class needs at least the following parameters:

* A secret encoded in Base32
* A digest algorithm
* A number of digits

Depending on the type of OTP, you will need the following additional parameters:

* For TOTP: a period
* For HOTP: a counter

## Secret

By default, a 512 bits secret is generated. If you need, you can use your own secret:

```php
<?php
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

$mySecret = trim(Base32::encodeUpper(random_bytes(128)), '='); // We generate our own 1024 bits secret
$otp = TOTP::create($mySecret);
```

## Period and Counter

By default, the period for a TOTP is 30 seconds and the counter for a HOTP is 0.

```php
<?php
use OTPHP\TOTP;
use OTPHP\HOTP;

$otp = TOTP::create(
    null, // Let the secret be defined by the class
    10    // The period is now 10 seconds
);

$otp = HOTP::create(
    null, // Let the secret be defined by the class
    1000  // The counter is now 1000. We recommend you start at `0`, but you can set any value (at least 0)
);
```

## Digest

By default the digest algorithm is `sha1`.
You can use any algorithm listed by [`hash_algos()`](http://php.net/manual/en/function.hash-algos.php).
Note that most applications only support `md5`, `sha1`, `sha256` and `sha512`.
You must verify that the algorithm you want to use is supported by the application your clients might be using.

`SHA-2` algorithms are recommended.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::create(
    null,       // Let the secret be defined by the class
    30,         // The period (30 seconds)
    'ripemd160' // The digest algorithm
);
```

## Digits

By default the number of digits is 6.
You can decide to use more (or less) digits. More than 10 may be difficult to use by the owner.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::create(
    null,   // Let the secret be defined by the class
    30,     // The period (30 seconds)
    'sha1', // The digest algorithm
    8       // The output will generate 8 digits
);
```

## Custom parameters

OTP objects are able to support custom parameters.
These parameters are available in the provisioning URI or from the method `getParameter`.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::create('JBSWY3DPEHPK3PXP'); // New TOTP
$totp->setLabel('alice@google.com'); // The label
$totp->setParameter('foo', 'bar');

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar
```

### Issuer

As a user may have multiple OTP using the same label (e.g. the user email),
it is useful to set the issuer parameter to identify the service that provided the OTP.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::create('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setLabel('alice@google.com'); // The label (string)
$totp->setIssuer('My Service');
```

By default and [to be compatible with Google Authenticator](https://github.com/google/google-authenticator/wiki/Key-Uri-Format#label),
the issuer is set in the query parameters and as the label prefix.

```php
<?php
echo $totp->getProvisioningUri(); // Will return otpauth://totp/My%20Service%3Aalice%40google.com?issuer=My%20Service&secret=JBSWY3DPEHPK3PXP
```

If you do not want to get the issuer as a query parameter, you can remove it by using the method `setIssuerIncludedAsParameter(bool)`.

```php
<?php
$totp->setIssuerIncludedAsParameter(false);
echo $totp->getProvisioningUri(); // Will return otpauth://totp/My%20Service%3Aalice%40google.com?secret=JBSWY3DPEHPK3PXP
```

### Image

Some applications such as FreeOTP can load images from an URI (`image` parameter).

> Please note that at the moment, we cannot list applications that support this parameter.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::create('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setLabel('alice@google.com'); // The label (string)
$totp->setParameter('image', 'https://foo.bar/otp.png');

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&image=https%3A%2F%2Ffoo.bar%2Fotp.png
```

When you load a QR Code using this input data, a compatible application will try to load the image at `https://foo.bar/otp.png`.
