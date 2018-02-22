scheb/two-factor-bundle
=======================

[![Build Status](https://travis-ci.org/scheb/two-factor-bundle.svg?branch=master)](https://travis-ci.org/scheb/two-factor-bundle)
[![Coverage Status](https://coveralls.io/repos/scheb/two-factor-bundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/scheb/two-factor-bundle?branch=master)
[![Latest Stable Version](https://poser.pugx.org/scheb/two-factor-bundle/v/stable.svg)](https://packagist.org/packages/scheb/two-factor-bundle)
[![License](https://poser.pugx.org/scheb/two-factor-bundle/license.svg)](https://packagist.org/packages/scheb/two-factor-bundle)

This bundle provides **two-factor authentication for your Symfony application**. It comes with the following two-factor authentication
methods:

- Google Authenticator (via sonata-project/google-authenticator)
- Authentication code sent via email

Additional features you will like:
- Interface for custom two-factor authentication methods
- Multi-factor authentication
- Trusted IPs
- Trusted devices (once passed, no more two-factor authentication on that device)
- Single-use backup codes to pass two-factor authentication, even when you don't have access to the second factor device


Compatibility
-------------
- **Recommended version**: Bundle version 3.x is compatible with Symfony 3.4 and 4.x
- Use bundle version 2.x for Symfony < 3.4
- Use bundle version 1.x for Symfony < 2.6

Documentation
-------------
Detailled documentation can be found in the [Resources/doc](Resources/doc/index.md) directory.

Contribute
----------
You're welcome to [contribute](https://github.com/scheb/two-factor-bundle/graphs/contributors) to this bundle by creating a pull
requests or feature request in the issues section.

Besides new features, [translations](Resources/translations) are highly welcome.

License
-------
This bundle is available under the [MIT license](LICENSE).
