# Upgrade from `v8.x` to `v9.x`

* All classes are now private.
* Minimal PHP version: `7.1`.
* Strict type hinting for all methods (including protected and private)
* Polyfills removed.
* `HHVM` is not supported nor tested anymore.
* Constructors are now private. You have to use provided static methods to create an object.
    * `HOTP::create()`
    * `TOTP::create()`
