scheb/two-factor-bundle
=======================

[![Build Status](https://travis-ci.org/scheb/two-factor-bundle.svg?branch=master)](https://travis-ci.org/scheb/two-factor-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/scheb/two-factor-bundle/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/scheb/two-factor-bundle/v/stable.svg)](https://packagist.org/packages/scheb/two-factor-bundle)
[![License](https://poser.pugx.org/scheb/two-factor-bundle/license.svg)](https://packagist.org/packages/scheb/two-factor-bundle)

This bundle provides **two-factor authentication for your Symfony application**. It comes with the following two-factor
authentication methods:

- Google Authenticator
- Email authentication code

Additional features you will like:
- Interface for custom two-factor authentication methods
- Trusted IPs
- Trusted devices (once passed, no more two-factor authentication on that device)
- Single-use backup codes for when you don't have access to the second factor device
- Multi-factor authentication

Installation
-------------

```bash
composer require scheb/two-factor-bundle
```

... and follow the [installation instructions](Resources/doc/installation.md).

Documentation
-------------
Detailed documentation of all features can be found in the [Resources/doc](Resources/doc/index.md) directory.

Compatibility
-------------
- **Recommended version**: Bundle version 3.x is compatible with Symfony 3.4 and 4.x
- Use bundle version 2.x for Symfony < 3.4
- Use bundle version 1.x for Symfony < 2.6

Security
--------
Before version 3.7 the bundle is vulnerable to a
[security issue in JWT](https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/), which can be
exploited by an attacker to generate trusted device cookies on their own, effectively by-passing two-factor
authentication.

Contribute
----------
You're welcome to [contribute](https://github.com/scheb/two-factor-bundle/graphs/contributors) to this bundle by
creating a pull requests or feature request in the issues section.

Besides new features, [translations](Resources/translations) are highly welcome.

To run the test suite install the dependencies with `composer install` and then execute `bin/phpunit`.

License
-------
This bundle is available under the [MIT license](LICENSE).
