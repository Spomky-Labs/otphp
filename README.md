# TOTP / HOTP library in PHP #

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/badges/quality-score.png?s=a184d180414b30764d19b421a12d6cf7e9e5e7c2)](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/)
[![Code Coverage](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/badges/coverage.png?s=d1bd1b26b56e581d6a4d1deb87eaadc51a05f31d)](https://scrutinizer-ci.com/g/Spomky-Labs/otphp/)

[![Build Status](https://travis-ci.org/Spomky-Labs/otphp.svg?branch=3.0.x)](https://travis-ci.org/Spomky-Labs/otphp)
[![HHVM Status](http://hhvm.h4cc.de/badge/Spomky-Labs/otphp.png)](http://hhvm.h4cc.de/package/Spomky-Labs/otphp)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/49e5925d-0dd8-4b89-a215-5eb33b4d96d9/big.png)](https://insight.sensiolabs.com/projects/49e5925d-0dd8-4b89-a215-5eb33b4d96d9)

[![Dependency Status](https://www.versioneye.com/user/projects/5388939114c158fa84000005/badge.svg)](https://www.versioneye.com/user/projects/5388939114c158fa84000005)

[![Latest Stable Version](https://poser.pugx.org/spomky-labs/otphp/v/stable.png)](https://packagist.org/packages/spomky-labs/otphp) [![Total Downloads](https://poser.pugx.org/spomky-labs/otphp/downloads.png)](https://packagist.org/packages/spomky-labs/otphp) [![Latest Unstable Version](https://poser.pugx.org/spomky-labs/otphp/v/unstable.png)](https://packagist.org/packages/spomky-labs/otphp) [![License](https://poser.pugx.org/spomky-labs/otphp/license.png)](https://packagist.org/packages/spomky-labs/otphp)

A php library for generating one time passwords according to [ RFC 4226 ](http://tools.ietf.org/html/rfc4226) (HOTP Algorithm) and the [ RFC 6238 ](http://tools.ietf.org/html/rfc6238) (TOTP Algorithm)

This library is compatible with Google Authenticator apps available for Android and iPhone. It is also compatible with other applications such as [FreeOTP](https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp) for example.

This is a fork of https://github.com/lelag/otphp with the following changes:

* Continuous unit and functional tests using Travis-CI
* 100% test coverage
* Code Quality improvement
* Better namespace usage
* Test coverage and code quality information
* Issuer support
* Completely abstract objects

## Prerequisites ##

This library needs at least `PHP 5.3`.
It has been successfully tested using `PHP 5.3` to `PHP 5.6` and `HHVM`

## Installation ##

The preferred way to install this library is to rely on Composer:

    {
        "require": {
            // ...
            "spomky-labs/otphp": "3.*"
        }
    }

## Extend the library ##

This library only contains the logic. You must extend all classes to define setters and getters.

Look at [Extend classes](doc/Extend.md) for more informations and examples.

## How to use ##

Your classes are ready to use? Have a look at [How to use](doc/Use.md) to generate your first OTP.

## Contributing

Requests for new features, bug fixed and all other ideas to make this library usefull are welcome. [Please follow these best practices](doc/Contributing.md).

## Licence

This software is release under MIT licence.
