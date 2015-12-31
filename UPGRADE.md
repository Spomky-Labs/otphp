Upgrading
=========

Here's an overview if you have to do any work when upgrading.
 
## 1.x to 2.x

Dropped support for Symfony < 2.6 and added support for Symfony 3.x.

## 1.2.0 to 1.3.0

The internal implementation of the Google Authentication and email authentication method has been re-factored. If you depend on internal classes, please check if your code is affected.

## 0.3.0 to 1.0.0

Changed case of parameter from `$GoogleAuthenticatorSecret` to `$googleAuthenticatorSecret` in `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`

## 0.1.0 to 0.1.1

The default authentication form template requires a route `_security_logout` to be defined, that directs to your logout URL. Alternatively you can configure a custom template.
