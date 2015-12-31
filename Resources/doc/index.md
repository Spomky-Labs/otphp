scheb/two-factor-bundle
=======================

This Symfony bundle provides two-factor authentication for your website.

By enabling the bundle it will hook into the authentication process and listen for authentication events. If the user entity supports two-factor authentication, it will not grant access until the user enters a valid authentication code.

The bundle supports multiple authentication methods.

## Index ##

  - [Installation](installation.md)
  - [Configuration](configuration.md)
  - [Trusted computers](trusted_computer.md)
  - [Backup codes](backup_codes.md)
  - [Creating a custom authentication method](custom.md)
  - [Creating a custom persister](persister.md)

## Authentication Methods ##

The bundle supports the following authentication methods out of the box:

  - [Google Authenticator](google.md)
  - [Authentication code sent via email](email.md)

## Custom authentication methods ##

If you want to implement you own custom authentication method (e.g. SMS code, PIN), you can do so by creating a two-factor provider, you can read more on how to create a custom authentication method [here](custom.md).

## Multi-level Authentication ##

You can also enable multiple authentication methods at the same time. This allows you to create a multi-level authentication process (three-factor and even more).
