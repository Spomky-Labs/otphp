scheb/two-factor-bundle
=======================

This Symfony bundle provides two-factor authentication for your website. Currently it is shipped with two authentication methods:

  - Google Authenticator (via sonata-project/google-authenticator)
  - Authentication code sent via email

In addition to this it provides an interface for implementing your own custom two-factor authentication methods.

**Compatibility:** Use bundle version 1.x for Symfony < 2.6.

[![Build Status](https://travis-ci.org/scheb/two-factor-bundle.svg?branch=master)](https://travis-ci.org/scheb/two-factor-bundle)
[![PHP 7 ready](http://php7ready.timesplinter.ch/scheb/two-factor-bundle/badge.svg)](https://travis-ci.org/scheb/two-factor-bundle)
[![Coverage Status](https://coveralls.io/repos/scheb/two-factor-bundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/scheb/two-factor-bundle?branch=master)
[![Latest Stable Version](https://poser.pugx.org/scheb/two-factor-bundle/v/stable.svg)](https://packagist.org/packages/scheb/two-factor-bundle)
[![License](https://poser.pugx.org/scheb/two-factor-bundle/license.svg)](https://packagist.org/packages/scheb/two-factor-bundle)

Limitations
-----------
After the initial login happened, the user is already fully authenticated to the Symfony security layer. The bundle
then prevents access to secured and non-secured content by intercepting any request and showing the two-factor
authentication form instead.

If you execute code based on the authentication status, make sure to take the two-factor status into account. This can
be done by checking access with `isGranted` (security voter has to be registered, see
[configuration](Resources/doc/configuration.md)).

**Warning**: Just doing a `getUser` on `security.token_storage` (or the old `security.context`) is not secure. You will
get a user object even when two-factor authentication is not complete yet.

Documentation
-------------
The documentation can be found in the [Resources/doc](Resources/doc/index.md) directory.

Contribute
----------
You're welcome to [contribute](https://github.com/scheb/two-factor-bundle/graphs/contributors) to this bundle by creating a pull requests or feature request in the issues section.

Besides new features, [translations](Resources/translations) are highly welcome.

License
-------
This bundle is available under the [MIT license](LICENSE).
