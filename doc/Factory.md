# The Factory

In some cases, you want to load a provisioning URI and get the corresponding OTP object.
That is why we created a factory.

```php
<?php
use OTPHP\Factory;

$otp = Factory::loadFromProvisioningUri('otpauth://totp/alice%40google.com?secret=JBSWY3DPEHPK3PXP&foo=bar');

// The variable $otp is now a valid TOTPInterface or HOTPInterface object with all parameters set (including custom parameters)
```
