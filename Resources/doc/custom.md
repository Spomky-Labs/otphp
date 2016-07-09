Implement a custom authentication method
========================================

## Getting started ##

A good starting point is the Google Authenticator and email authentication method, which is shipped with this bundle. Take a look at at the classes located in those namespaces:

 - `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email`
 - `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google`

You will get the basic idea how to implement a custom two-factor method.

## The TwoFactorProviderInterface ##

You have to create a service, which implements the `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface` interface. It contains two methods:

### beginAuthentication ###

```php
/**
 * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface $context
 * @return boolean
 */
public function beginAuthentication(AuthenticationContextInterface $context);
```

The method is called after successful login. It receives an `AuthenticationContextInterface` object as the argument (see class `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext`) which contains the request object and the authentication token.

The method has to decide if the user should be asked for two-factor authentication. In that case return `true`, otherwise return `false`. You should also do the preparation work for the two-factor process. E.g. the email authenticator is generating a code and sending it to the user.

### requestAuthenticationCode ###

```php
/**
 * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface $context
 * @return \Symfony\Component\HttpFoundation\Response|null
 */
public function requestAuthenticationCode(AuthenticationContextInterface $context);
```

The method is called on each request, when the two-factor process has been started but not completed yet. It receives an `AuthenticationContextInterface` object as the argument (see class `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext`) which contains the request object and the authentication token.

You have to create a response, which will be shown to the user. In most cases you will display a form, which asks the user for the authentication code. When the form is submitted, you have to validate the code. If the code is correct, execute `setAuthenticated(true)` on the `AuthenticationContextInterface` object. This will complete the two-factor authentication.

## Register the Provider ##

Now you have to register your provider as a service. A tag named `scheb_two_factor.provider` will make your provider available to SchebTwoFactorBundle. The attribute `alias` has to be a unique identifier for the authentication provider.

```xml
<service id="acme.two_factor.provider" class="%acme.two_factor.provider.class%">
	<tag name="scheb_two_factor.provider" alias="acme_two_factor" />
</service>
```

**Please note**: The aliases `google` and `email` are already taken by the authentication methods shipped with this bundle.
