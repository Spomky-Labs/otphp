Configuration
=============

For detailed information see the documentation of the authentication methods.

```yaml
scheb_two_factor:
    email:
        enabled: true   # If email authentication should be enabled, default false
        mailer: my_mailer_service   # Use alternative service to send the authentication code
        sender_email: me@example.com   # Sender email address 
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form
    google:
        enabled: true   # If Google Authenticator should be enabled, default false
        server_name: Server Name   # Server name used in QR code
        template: AcmeDemoBundle:Authentication:form.html.twig   # Template used to render the authentication form
```
