Upgrading
=========

Here's an overview if you have to do any work when upgrading.

## 2.x to 3.x

Dropped support for Symfony < 3.4.

The `exclude_path` configuration option has been removed, please use firewall `access_control` instead.

The following public interfaces have been extended with PHP7 type hints. Please upgrade method signatures in your implementations.
- `Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface`
- `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`
- `Scheb\TwoFactorBundle\Model\BackupCodeInterface`
- `Scheb\TwoFactorBundle\Model\PersisterInterface`
- `Scheb\TwoFactorBundle\Model\TrustedComputerInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface`
- `Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManagerInterface`

## 1.x to 2.x

Dropped support for Symfony < 2.6 and added support for Symfony 3.x.

## 1.2.0 to 1.3.0

The internal implementation of the Google Authentication and email authentication method has been re-factored. If you depend on internal classes, please check if your code is affected.

## 0.3.0 to 1.0.0

Changed case of parameter from `$GoogleAuthenticatorSecret` to `$googleAuthenticatorSecret` in `Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface`

## 0.1.0 to 0.1.1

The default authentication form template requires a route `_security_logout` to be defined, that directs to your logout URL. Alternatively you can configure a custom template.
