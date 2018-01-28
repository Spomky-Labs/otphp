Installation
============

## Prerequisites

If you're using anything other than Doctrine ORM to manage the User Entity you will have to implement a [persister service](persister.md).

## Installation

### Step 1: Download using Composer

Add this bundle via Composer:

```bash
php composer.phar require scheb/two-factor-bundle
```

### Step 2: Enable the bundle

Enable this bundle in your `config/bundles.php`:

```php
return [
	// ...
    Scheb\TwoFactorBundle\SchebTwoFactorBundle::class => ['all' => true],
];
```

### Step 3: Define routes

In `config/routes.yaml` add a route for the two-factor authentication form and another one for checking the authentication code.

```yaml
2fa_login:
    path: /2fa
    defaults:
        _controller: "SchebTwoFactorBundle:Authentication:form"

2fa_login_check:
    path: /2fa_check
```

### Step 4: Configure the firewall

Enable two-factor authentication per firewall and configure access_control for the 2fa routes:

```yaml
security:
    firewalls:
        main:
            two-factor:
                auth_form_path: 2fa_login               # The route name you have used in the routes.yaml
                check_path: 2fa_login_check             # The route name you have used in the routes.yaml
                always_use_default_target_path: false   # Optional, default is false
                default_target_path: /                  # Optional, can be a path or a route name

    # This ensures that the form can only be accessed when two-factor authentication is in progress
    access_control:
        - { path: ^/2fa, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }
```

### Step 5: Register authentication tokens

Your firewall may offer different ways how to login. By default the bundle is only listening to the user-password authentication
(which uses the token class `Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken`).
If you want to support two-factor authentication with another login method, you have to register its token class in the
`scheb_two_factor.security_tokens` configuration option.

```yaml
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
        - Acme\AuthenticationBundle\Token\CustomAuthenticationToken
```

### Step 6: Enable two-factor authentication methods

The two-factor authentication methods need to be enabled separately. Read how to do this for [Google Authenticator](google.md)
or [email authentication](email.md).

### Step 7: Detailed configuration

Finally, you probably want to configure some details of the bundle. See the [all configuration options](configuration.md).
