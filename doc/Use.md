## How to Use

### Create a Base32 encoded secret

This library depends on the Base32 library which provides an easy way to encode or decode a string into Base32.

    $my_secret = "This is my random string";
    $base32_secret = Base32\Base32::encode($my_secret);

### Time based OTP's

The creation of a TOTPobject is really easy:

    $totp = new OTPHP\TOTP($base32_secret);

If your secret is malformed, the library will sanitize it. For example, your secret is ```éç,/JDDK4U6G3.;!BJLEZ7YàÊà```, it will become ```JDDK4U6G3BJLEZ7Y```.

Now, your TOTP object can calculate your passwords:

    $totp->now(); // => 492039

The password can be verified for current time:

    $totp->verify(492039); // => true

And 30s later:

    $totp->verify(492039); // => false

You can also get and verify a password for a specific time:

	$password = $totp->at(time()+ 3600);
    $totp->verify($password, time()); // => false
    $totp->verify($password, time()+ 3600); // => true

### Counter based OTP's

The HOTP object is very easy to use too:

    $hotp = new OTPHP\HOTP($base32_secret);

Now you can calculate password:

    $hotp->at(0); // => 260182
    $hotp->at(1); // => 55283
    $hotp->at(1401); // => 316439

OTP verified with a counter

    $totp->verify(316439, 1401); // => true
    $totp->verify(316439, 1402); // => false

### Google Authenticator Compatible

The library works with the Google Authenticator iPhone and Android app, and also
includes the ability to generate provisioning URI's for use with the QR Code scanner
built into the app.

    $totp->setLabel("alice@google.com");
    $totp->provisioningURI(); // => 'otpauth://totp/alice%40google.com?algorithm=sha1&digits=6&period=30&secret=JBSWY3DPEHPK3PXP'
    $hotp->provisioningURI(); // => 'otpauth://hotp/alice%40google.com?algorithm=sha1&counter=0&digits=6&secret=JBSWY3DPEHPK3PXP&counter=0'

Google only supports SHA-1 digest algorithm, 30 second interval and 6 digits OTP. These parameters are ignored by the Google Authenticator application.

### Options

HOTP and TOTP options object can be modified to produce different passwords. By default, they are compatible with Google Authenticator, but feel free to use them with an other application that supports these options such as FreeOTP on Android.

#### Common options

##### Digest Algorithm

The following digest algorithm are supported:
* md5
* sha1 (default)
* sha256
* sha512

    $totp->setDigest('sha256');
    $totp->getDigest(); //Return 'sha256'

##### Digits

OTP generated must have at least 1 digit. By default they have 6 digits.

    $totp->setDigit(8);
    $totp->getDigit(); //Return 8

##### Label

A label can be added to the OTP. This label is mandatory when provisioning URI is generated.

    $totp->setLabel('alice@google.com');
    $totp->getLabel(); //Return 'alice@google.com'

##### Issuer

An issuer can be added to the OTP. By default this value is null. This issuer only is included in provisioning URI. It is strongly recommended to add it:

    $totp->setIssuer('My Application');
    $totp->getIssuer(); //Return 'My Application'

#### Counter based OTP's (HOTP) options

##### Initial Counter

By default, the initial counter is 0. You can modify it with the folling method:

    $hotp->setInitialCount(500);
    $hotp->getInitialCount(); // Return 500

#### Time based OTP's (TOTP) options

##### Initial Counter

This option modifies the interval of time for each OTP. 

    $hotp->setInterval(10);
    $hotp->getInterval(); // Return 10

Counter based OTP's can

This can then be rendered as a QR Code which can then be scanned and added to the users
list of OTP credentials.

### Working examples

### Compatible with Google Authenticator

Scan the following barcode with your phone, using Google Authenticator

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Falgorithm%3Dsha1%26digits%3D6%26period%3D30%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

    $totp = new OTPHP\TOTP("JBSWY3DPEHPK3PXP");
    echo "Current OTP: ". $totp->now();

### Not Compatible with Google Authenticator

The following barcode will not work with Google Authenticator because digest algoritm is not SHA-1, there are 8 digits and counter is not 30 seconds.

![QR Code for OTP](http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%3Aalice%2540google.com%3Falgorithm%3Dsha512%26digits%3D8%26period%3D10%26secret%3DJBSWY3DPEHPK3PXP%26issuer%3DMy%2520Big%2520Compagny)

Now run the following and compare the output

    $totp = new OTPHP\TOTP("JBSWY3DPEHPK3PXP");
    $totp->setInterval(10);
    $totp->setDigest('sha512');
    $totp->setDigits(8);

    echo "Current OTP: ". $totp->now();
