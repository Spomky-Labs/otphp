# Application Configuration

Applications that support OTPs are, in general, able to easily configure an OTP.
This configuration is possible through a provisioning URI that contains all OTP's parameters.
Usually, that provisioning URI is loaded by the application using a QR Code.

This library is able to create provisioning URIs according to the OTP parameters.

You just have to:

- make sure that a label is defined (see `setLabel` method)
- call the method `getProvisioningUri`.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setLabel('alice@google.com'); // The label (string)

$totp->getProvisioningUri(); // Will return otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP
```

The provisioning URI is used as the QR Code content.
Some online services allow you to generate QR Codes that you can integrate into your website.

> Please note that online services may be unsecured.
> Before using a QR Code generator service, you should ensure the created images are not cached or logged to avoid potential leaks.
> When possible, we recommend you to use your own QR Code generator.

Hereafter two examples using the Google Chart API (this API is deprecated since April 2019):

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::generate(); // New TOTP
$totp->setLabel('alice@google.com'); // The label (string)

$google_chart = $totp->getQrCodeUri('https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={PROVISIONING_URI}', '{PROVISIONING_URI}');
echo "<img src='{$google_chart}'>";
```

If you want to use another QR Code Generator Service, just pass the URI as the first argument of `getQrCodeUri`.
Please note that this URI MUST contain a placeholder for the OTP Provisioning URI. By default this placeholder is `{PROVISIONING_URI}`, but you can change it with the second argument.

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::generate(); // New TOTP
$totp->setLabel('alice@google.com'); // The label (string)

$goqr_me = $totp->getQrCodeUri(
    'https://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=[DATA]&qzone=2&margin=0&size=300x300&ecc=M',
    '[DATA]'
);
echo "<img src='{$goqr_me}'>";
```

## Google Authenticator Example

The library works with the Google Authenticator application for iPhone and Android.

Google only supports SHA-1 digest algorithm, 30 second period and 6 digits OTP. Other values for these parameters are ignored by the Google Authenticator application.

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Company%3Aalice%2540google.com%3Fsecret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Company)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setLabel('alice@google.com'); // The label (string)

echo 'Current OTP: ' . $totp->now();
```

## Other Applications Example

The following barcode will not work with Google Authenticator because digest algorithm is SHA-512, there are 8 digits and the counter is 10 seconds. But it should work with other applications such as FreeOTP, which support those parameters.

![QR Code for OTP](https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Company%3Aalice%2540google.com%3Falgorithm%3Dsha512%26digits%3D8%26period%3D10%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Company)

Now run the following and compare the output

```php
<?php
use OTPHP\TOTP;

$totp = TOTP::createFromSecret('JBSWY3DPEHPK3PXP'); // New TOTP with custom secret
$totp->setPeriod(10);                   // The period (int)
$totp->setDigest('sha512');             // The digest algorithm (string)
$totp->setDigits(8);                    // The number of digits (int)
$totp->setLabel('alice@google.com');    // The label (string)

echo 'Current OTP: ' . $totp->now();
```
