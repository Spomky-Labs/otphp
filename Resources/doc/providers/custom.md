Implementing a custom two-factor authenticator
==============================================

## Getting started

A good starting point are the Google Authenticator and email authentication method, which are shipped with this bundle.
Take a look at at the classes located in those namespaces:

 - `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email`
 - `Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google`

You will get the basic idea how to implement a custom two-factor method.

## The TwoFactorProviderInterface

You have to create a service, which implements the
`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface` interface. It requires these methods:

### beginAuthentication

```php
public function beginAuthentication(AuthenticationContextInterface $context): bool
```

The method is called after successful login. It receives an `AuthenticationContextInterface` object as the argument
(see class `Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext`) which contains the request object the
authentication token, the user entity and other information.

The method has to decide if the user should be asked for two-factor authentication from that provider. In that case
return `true`, otherwise `false`.

You should also do the preparation work for the two-factor process in this method. E.g. the email authenticator is
generating a code and sending it to the user.

### validateAuthenticationCode

```php
public function validateAuthenticationCode($user, string $authenticationCode): bool
```

This method is responsible for validating the authentication code entered by the user. Return `true` if the code was
correct or `false` when it was wrong.

### getFormRenderer

```php
public function getFormRenderer(): TwoFactorFormRendererInterface;
```

This method has to provide a service for rendering the authentication form. Such a service has to implement the
`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface` interface:

```php
public function renderForm(Request $request, array $templateVars): Response;
```

How you render the form is totally up to you. The only important thing is to return a `Response`, which could also be a
`RedirectResponse` redirect to an external service. A default implementation for rendering forms with Twig is available as
`Scheb\TwoFactorBundle\Security\TwoFactor\Provider\DefaultTwoFactorFormRenderer`.

## Register the provider

Now you have to register your two-factor provider as a service. A tag named `scheb_two_factor.provider` will make your
provider available to the bundle.

The attribute `alias` has to be a unique identifier for the authentication provider.

```xml
<service id="acme.two_factor.provider" class="%acme.two_factor.provider.class%">
	<tag name="scheb_two_factor.provider" alias="acme_two_factor" />
</service>
```

**Please note**: The aliases `google` and `email` are already taken by the authentication methods shipped with this
bundle.
