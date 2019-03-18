# Upgrade from `v9.x` to `v10.x`

This new major release updates the dependencies and required atleast PHP 7.2.
The `ext-mbstring` is now required.

There is normally nothing to do if you use the `Factory`, `Totp` and `Hotp` classes directly.

The some changes concern:

* new public methods added to the interfaces.
* a new interface for the factory.
