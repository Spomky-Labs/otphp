Configuration
=============

For detailed information see the documentation of the authentication methods.

```yaml
scheb_two_factor:
    trusted_computer:
        enabled: false   # If the trusted computer feature should be enabled
        cookie_name: trusted_computer   # Name of the trusted computer cookie
        cookie_lifetime: 5184000    # Lifetime of the trusted computer cookie
    email:
        enabled: true   # If email authentication should be enabled, default false
        mailer: my_mailer_service   # Use alternative service to send the authentication code
        sender_email: me@example.com   # Sender email address 
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form
        digits: 4   # Number of digits in authentication code
    google:
        enabled: true   # If Google Authenticator should be enabled, default false
        server_name: Server Name   # Server name used in QR code
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form

    # If your Doctrine user object is managed by a model manager, which is not the default one, you have to
    # set this option
    model_manager_name: ~ # Name of entity manager or null, which uses the default one
```
