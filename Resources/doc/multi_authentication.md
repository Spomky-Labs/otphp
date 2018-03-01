Multiple Activated Authentication Methods
=========================================

A user can have multiple authentication methods enabled at the same time. You can choose if you want to have:
- a multi-level authentication process (three-factor and even more)
- or two-factor authentication and give the user the possibility to choose the authentication method

You can set the in the firewall configuration:

```yaml
security:
    firewalls:
        yourFirewallName:
            two_factor:
                multi_factor: false  # If ALL active two-factor methods need to be fulfilled
```

If you're not using multi-factor authentication, the user might want to configure a default two-factor method, which is
requested first, before switching to another two-factor method. You can provide the preferred two-factor method by
implementing the `Scheb\TwoFactorBundle\Model\PreferredProviderInterface` interface in the user entity. Return the
alias of the two-factor provider, for example `google` or `email` for the ones shipped with this bundle. If `null` is
returned the default order is applied.
