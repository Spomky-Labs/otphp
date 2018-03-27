Two-Factor Authentication Methods
=================================

# Built-in providers 
The bundle supports the following authentication methods out of the box:

  - [Google Authenticator](google.md)
  - [Email authentication code](email.md)

# Custom two-factor authenticator
 
If you want to implement your own authentication method (e.g. SMS code, PIN), you can do so by creating a two-factor
provider. Read how to create a [Implementing a custom two-factor authenticator](custom.md).

# Third-party providers

* [r/u2f-two-factor-bundle](https://github.com/darookee/u2f-two-factor-bundle) implements
 [yubico/u2flib-server](https://github.com/Yubico/php-u2flib-server) to support U2F - FIDO Universal 2nd Factor 
 Authentication. It is currently only [supported](https://caniuse.com/#search=u2f) by Chrome and Firefox.
