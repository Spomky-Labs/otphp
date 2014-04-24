Upgrading
=========

Here's an overview what has been changed between versions and if you have to do any work when upgrading. 

## 0.1.1 to 0.2.0

Nothing to upgrade

### Changes
 - Major code refactoring
 - Implemented an interface for custom two factor authentcation providers

## 0.1.0 to 0.1.1

The default authentication form template requires a route `_security_logout` to be defined, that directs to your logout URL. Alternatively you can configure a custom template.

### Changes
 - Bugfixes
 - Link added to cancel two factor authentication

