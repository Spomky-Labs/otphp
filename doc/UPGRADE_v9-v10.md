# Upgrade from `v9.x` to `v10.x`

This new major release updates the dependencies and required at least PHP 7.2.

For the upgrade from `v9.x` to `v10.x`, there is normally nothing to do.
The main changes only concern:

* new public methods added to the interfaces.
* a new interface for the factory.
* the parameters of the method `OTP::getQrCodeUri` are now required as the Google API is deprecated since April 2019. 

The library now relies on the MBString extension.
This extension is now a required dependency.
