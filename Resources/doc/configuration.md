Configuration
=============

```yaml
# config/packages/scheb_two_factor.yaml
scheb_two_factor:

    # Trusted device feature
    trusted_device:
        enabled: false                 # If the trusted device feature should be enabled
        manager: acme.custom_trusted_device_manager  # Use a custom trusted device manager
        lifetime: 5184000              # Lifetime of the trusted device token
        extend_lifetime: false         # Automatically extend lifetime of the trusted cookie on re-login
        cookie_name: trusted_device    # Name of the trusted device cookie
        cookie_secure: false           # Set the 'Secure' (HTTPS Only) flag on the trusted device cookie
        cookie_same_site: "lax"        # The same-site option of the cookie, can be "lax" or "strict"

    # Backup codes feature
    backup_codes:
        enabled: false                 # If the backup code feature should be enabled
        manager: acme.custom_backup_code_manager  # Use a custom backup code manager

    # POST/GET parameter names
    parameter_names:
        auth_code: _auth_code          # Name of the parameter containing the authentication code
        trusted: _trusted              # Name of the parameter containing the trusted flag

    # Email authentication config
    email:
        enabled: true                  # If email authentication should be enabled, default false
        mailer: acme.custom_mailer_service  # Use alternative service to send the authentication code
        sender_email: me@example.com   # Sender email address
        sender_name: John Doe          # Sender name
        digits: 4                      # Number of digits in authentication code
        template: security/2fa_form.html.twig   # Template used to render the authentication form

    # Google Authenticator config
    google:
        enabled: true                  # If Google Authenticator should be enabled, default false
        server_name: Server Name       # Server name used in QR code
        issuer: Issuer Name            # Issuer name used in QR code
        template: security/2fa_form.html.twig   # Template used to render the authentication form

    # The service which is used to persist data in the user object. By default Doctrine is used. If your entity is
    # managed by something else (e.g. an API), you have to implement a custom persister
    persister: acme.custom_persister

    # If your Doctrine user object is managed by a model manager, which is not the default one, you have to
    # set this option. Name of entity manager or null, which uses the default one.
    model_manager_name: ~

    # The security token classes, which trigger two-factor authentication.
    # By default the bundle only reacts to Symfony's username+password authentication. If you want to enable
    # two-factor authentication for other authentication methods, add their security token classes.
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken

    # A list of IP addresses, which will not trigger two-factor authentication
    ip_whitelist:
        - 127.0.0.1
```

```yaml
# config/packages/security.yaml
security:
    firewalls:
        firewallName:
            # ...
            two_factor:
                auth_form_path: /2fa                   # Path or route name of the two-factor form
                check_path: /2fa_check                 # Path or route name of the two-factor code check
                default_target_path: /                 # Where to redirect by default after successful authentication
                always_use_default_target_path: false  # If it should always redirect to default_target_path
                auth_code_parameter_name: _auth_code   # Name of the parameter for the two-factor authentication code
                trusted_parameter_name: _trusted       # Name of the parameter for the trusted device option
```

For detailed information see the documentation of the authentication methods:
- [Google Authenticator](provider_google.md)
- [Email code](provider_email.md).
