# OTP Customization

To generate one-time passwords, each class needs at least the following parameters:

* A secret encoded in Base32
* A digest algorithm
* A number of digits

Depending on the type of OTP, you will need the following additional parameters:

* For TOTP: a period (and optionally an epoch)
* For HOTP: a counter

## Secret

By default, a 512 bits secret is generated. If you need, you can use your own secret:

```php
<?php
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

$mySecret = trim(Base32::encodeUpper(random_bytes(128)), '='); // We generate our own 1024 bits secret
$otp = TOTP::createFromSecret($mySecret);
```

*Please note that the trailing `=` are automatically removed by the library.*

## Period and Counter

By default, the period for a TOTP is 30 seconds and the counter for a HOTP is 0.

```php
<?php
use OTPHP\TOTP;
use OTPHP\HOTP;

$otp = TOTP::generate();
$otp->setPeriod(10);    // The period is now 10 seconds

$otp = HOTP::generate();
$otp->setCounter(1000); // The counter is now 1000. We recommend you start at `0`, but you can set any value (at least 0)
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

$totp = TOTP::generate();
$totp->setPeriod(30);           // The period (30 seconds)
$totp->setDigest('ripemd160');  // The digest algorithm
```

## Digits

By default the number of digits is 6.
You can decide to use more (or less) digits. More than 10 may be difficult to use by the owner.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::generate();
$totp->setPeriod(30);       // The period (30 seconds)
$totp->setDigest('sha1');   // The digest algorithm
$totp->setDigits(8);        // The output will generate 8 digits
```

## Epoch (TOTP only)

By default, the epoch for a TOTP is `0`.
The epoch is equivalent to the `T0` parameter in [RFC 6238](https://tools.ietf.org/html/rfc6238#page-4).
This parameter basically determines at which timestamp (epoch) to start counting. It is useful in scenarios where
you need an exact period to verify passwords in. The epoch can be shared by client and server to specify the exact
timestamp at which the password was created so that you can reuse it for exact verification.

**CAUTION:** If you follow this approach and share the epoch as password creation timestamp, you should use dynamic
secrets that are different each time, otherwise you will most likely always produce the same passwords. You could for
example encode the timestamp in the secret to make it different each time.

```php
<?php
use OTPHP\TOTP;

// Without epoch
$otp = TOTP::generate();
$otp->setPeriod(5);         // The period (5 seconds)
$otp->setDigest('sha1');    // The digest algorithm
$otp->setDigits(6);         // The output will generate 6 digits

$password = $otp->at(1519401289); // Current period is: 1519401285 - 1519401289

$otp->verify($password, 1519401289); // Second 1: true
$otp->verify($password, 1519401290); // Second 2: false

// With epoch
$otp = TOTP::generate();
$otp->setPeriod(5);         // The period (30 seconds)
$otp->setDigest('sha1');    // The digest algorithm
$otp->setDigits(6);         // The output will generate 8 digits
$otp->setEpoch(1519401289); // The epoch is now 02/23/2018 @ 3:54:49pm (UTC)

$password = $otp->at(1519401289);  // Current period is: 1519401289 - 1519401293

$otp->verify($password, 1519401289); // Second 1: true
$otp->verify($password, 1519401290); // Second 2: true
$otp->verify($password, 1519401291); // Second 3: true
$otp->verify($password, 1519401292); // Second 4: true
$otp->verify($password, 1519401293); // Second 5: true
$otp->verify($password, 1519401294); // Second 6: false
```

## Custom parameters

OTP objects are able to support custom parameters.
These parameters are available in the provisioning URI or from the method `getParameter`.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP
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

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
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

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setLabel('alice@google.com'); // The label (string)
$totp->setParameter('image', 'https://foo.bar/otp.png');

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&image=https%3A%2F%2Ffoo.bar%2Fotp.png
```

When you load a QR Code using this input data, a compatible application will try to load the image at `https://foo.bar/otp.png`.
