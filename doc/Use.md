# How to Use

## Common methods

TOTP and HOTP objects have the following common methods:

* ```public function at($input);```: generate an OTP at the specified counter
* ```public function verify($otp, $input, $window);```: verify if the OTP is valid for the specified input (timestamp, counter...)
* ```public function getProvisioningUri()```: return a provisioning URI to ease integration in applications

Example:

```php
$my_otp_object->at(1000); //e.g. will return 123456
$my_otp_object->verify(123456, 1000); //Will return true
$my_otp_object->verify(123456, 1001); //Will return false
```

## Counter Based OTP (HOTP)

### The window parameter

The `windows` parameter will try all OTP within a window of counters.

By default, this value is `null`. It means that the OTP will be tested at the exact counter.

If the parameter is an integer, the OTP from `counter` to `counter+window` will be tested.
For example, if the `counter`is `1000` and the window `10`, the OTP tested are within `1000` and `1010`.

```php
$my_otp_object->verify(123456, 999); //Will return false
$my_otp_object->verify(123456, 999, 10); //Will return true (1000 is tested)
```

If the verification succeed, the counter will be updated.

## Time Based OTP (TOTP)

This OTP object has a specific method:

* ```public function now()```: return an OTP at the current timestamp

Example:

```php
$my_otp_object->now(); //e.g. will return 123456
$my_otp_object->verify(123456); //Will return true.
```
    
After the interval defined by the object:

```php
$my_otp_object->verify(123456); //Will return false
```

### The window parameter

The `windows` parameter will try all OTP within a window of timestamps.

By default, this value is `null`. It means that the OTP will be tested at the exact timestamp.
If the parameter is an integer, the OTP before and after the timestamp will be tested.

The window of timestamps goes from `- $window * interval + timestamp` to `+ $window * interval + timestamp`. For example, if the `window`is `5`, the interval `30` and the timestamp `600`, the OTP tested are within `450` and `750`.

### Google Authenticator Compatible

The library works with the Google Authenticator iPhone and Android app, and also
includes the ability to generate provisioning URI's for use with the QR Code scanner
built into the app.

Google only supports SHA-1 digest algorithm, 30 second interval and 6 digits OTP. Other values for these parameters are ignored by the Google Authenticator application.

```php
<?php
use MyProject\TOTP;

$totp = new TOTP;
$totp->setLabel("alice@google.com")
     ->setDigits(6)
     ->setDigest('sha1')
     ->setInterval(30)
     ->setSecret("JBSWY3DPEHPK3PXP");

$totp->getProvisioningUri(); // => 'otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP'
```

### Working examples

#### Compatible with Google Authenticator

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Fsecret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

```php
<?php
use MyProject\TOTP;

$totp = new TOTP;
$totp->setLabel("alice@google.com")
     ->setDigits(6)
     ->setDigest('sha1')
     ->setInterval(30);
     ->setSecret("JBSWY3DPEHPK3PXP");

echo "Current OTP: ". $totp->now();
```

#### Not Compatible with Google Authenticator

The following barcode will not work with Google Authenticator because digest algoritm is not SHA-1, there are 8 digits and counter is not 30 seconds.

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Falgorithm%3Dsha512%26digits%3D8%26period%3D10%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

```php
<?php
use MyProject\TOTP;

$totp = new TOTP;
$totp->setLabel("alice@google.com")
     ->setDigits(8)
     ->setDigest('sha512')
     ->setInterval(10)
     ->setSecret("JBSWY3DPEHPK3PXP");

echo "Current OTP: ". $totp->now();
```
