scheb/two-factor-bundle
=======================

This bundle provides **two-factor authentication for your Symfony application**.

The bundle hocks into security layer and listens for authentication events. When a user authenticates and has two-factor
authentication enabled, access is only granted if the user can enter a valid two-factor authentication code.
 
## Index

- [Installation](installation.md)
- [Configuration Reference](configuration.md)
- [Trusted Devices](trusted_device.md)
- [Backup Codes](backup_codes.md)
- [Brute Force Protection](brute_force_protection.md)
- [How to create a custom two-factor authenticator](provider_custom.md)
- [How to handle multiple activated authentication methods](multi_authentication.md)
- [How to create a custom persister](persister.md)

## Two-Factor Authentication Methods

The bundle supports the following authentication methods out of the box:

  - [Google Authenticator](provider_google.md)
  - [Email authentication code](provider_email.md)

If you want to implement your own authentication method (e.g. SMS code, PIN), you can do so by creating a two-factor
provider. Read how to create a [custom two-factor authenticator](provider_custom.md).
