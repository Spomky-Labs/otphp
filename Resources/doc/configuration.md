Configuration
=============

For detailed information see the documentation of the authentication methods, [Google Auth](google.md) and [Email](email.md).

```yaml
# config/packages/scheb_two_factor.yaml
scheb_two_factor:

    # Trusted computer feature
    trusted_computer:
        enabled: false                 # If the trusted computer feature should be enabled
        cookie_name: trusted_computer  # Name of the trusted computer cookie
        cookie_lifetime: 5184000       # Lifetime of the trusted computer cookie
        cookie_secure: false           # Set the 'Secure' (HTTPS Only) flag on the trusted_computer cookie
        cookie_same_site: "lax"        # The same-site option of the cookie, can be "lax" or "strict"

    # Regex pattern of paths you want to exclude from two-factor authentication.
    # Useful to exclude Assetic paths or other files from being blocked.
    # Example: ^/(css|js|images)/
    exclude_pattern: ~

    # POST/GET parameter names
    parameter_names:
        auth_code: _auth_code          # Name of the parameter containing the authentication code
        trusted: _trusted              # Name of the parameter containing the trusted flag

    # Email authentication config
    email:
        enabled: true                  # If email authentication should be enabled, default false
        mailer: my_mailer_service      # Use alternative service to send the authentication code
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
    persister: scheb_two_factor.persister.doctrine

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
