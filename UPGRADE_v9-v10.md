# Upgrade from `v9.x` to `v10.x`

This new major release updates the dependencies and required atleast PHP 7.2.

For the upgrade from `v9.x` to `v10.x`, there is normally nothing to do.
The main changes only concern:

* new public methods added to the interfaces.
* a new interface for the factory.

The library now relies on the MBString extension. This extension is now a required dependency.
