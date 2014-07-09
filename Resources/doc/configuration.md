Configuration
=============

For detailed information see the documentation of the authentication methods.

```yaml
scheb_two_factor:

    # Trusted computer feature
    trusted_computer:
        enabled: false                 # If the trusted computer feature should be enabled
        cookie_name: trusted_computer  # Name of the trusted computer cookie
        cookie_lifetime: 5184000       # Lifetime of the trusted computer cookie

    # POST/GET parameter names
    parameter_names:
        auth_code: _auth_code          # Name of the parameter containing the authentication code
        trusted: _trusted              # Name of the parameter containing the trusted flag

    # Email authentication config
    email:
        enabled: true                  # If email authentication should be enabled, default false
        mailer: my_mailer_service      # Use alternative service to send the authentication code
        sender_email: me@example.com   # Sender email address 
        digits: 4                      # Number of digits in authentication code
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form

    # Google Authenticator config
    google:
        enabled: true                  # If Google Authenticator should be enabled, default false
        server_name: Server Name       # Server name used in QR code
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form
    
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
```