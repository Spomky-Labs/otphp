# OTPHP - A PHP One Time Password Library

A php library for generating one time passwords according to [ RFC 4226 ](http://tools.ietf.org/html/rfc4226) and the [ HOTP RFC ](http://tools.ietf.org/html/draft-mraihi-totp-timebased-00)

This is compatible with Google Authenticator apps available for Android and iPhone, and now in use on GMail

This is a port of the rotp ruby library available at https://github.com/mdp/rotp


## Quick overview of using One Time Passwords on your phone

* OTP's involve a shared secret, stored both on the phone and the server
* OTP's can be generated on a phone without internet connectivity(AT&T mode)
* OTP's should always be used as a second factor of authentication(if your phone is lost, you account is still secured with a password)
* Google Authenticator allows you to store multiple OTP secrets and provision those using a QR Code(no more typing in the secret)

## Installation

   clone this repository and include lib/otphp.php in your project. 

## Use

### Time based OTP's

    $totp = new \OTPHP\TOTP("base32secret3232");
    $totp->now(); // => 492039

    // OTP verified for current time
    $totp->verify(492039); // => true
    //30s later
    $totp->verify(492039); // => false

### Counter based OTP's

    $hotp = new \OTPHP\HOTP("base32secretkey3232");
    $hotp->at(0); // => 260182
    $hotp->at(1); // => 55283
    $hotp->at(1401); // => 316439

    // OTP verified with a counter
    $totp->verify(316439, 1401); // => true
    $totp->verify(316439, 1402); // => false

### Google Authenticator Compatible

The library works with the Google Authenticator iPhone and Android app, and also
includes the ability to generate provisioning URI's for use with the QR Code scanner
built into the app.

    $totp->provisioning_uri(); // => 'otpauth://totp/alice@google.com?secret=JBSWY3DPEHPK3PXP'
    $hotp->provisioning_uri(); // => 'otpauth://hotp/alice@google.com?secret=JBSWY3DPEHPK3PXP&counter=0'

This can then be rendered as a QR Code which can then be scanned and added to the users
list of OTP credentials.

#### Working example

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2Falice%40google.com%3Fsecret%3DJBSWY3DPEHPK3PXP)

Now run the following and compare the output

    <?php
    require_once('otphp/lib/otphp.php');
    $totp = new \OTPHP\TOTP("JBSWY3DPEHPK3PXP");
    echo "Current OTP: ". $totp->now();

## Licence

This software is release under MIT licence.
