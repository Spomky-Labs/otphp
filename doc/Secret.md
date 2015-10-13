How to generate a secret
========================

The secret used by an OTP is a base32 encoded string.

You have 2 options:

1. You can generate a random (binary) string and convert it into a base32 string
2. You can directly generate a random string using base32 charset

Hereafter two examples. We recommand you to use the first solution as it does not add new dependency, but **we highly recommend you to use a secure string generator**.

# Example 1

In this example, we will generate a string and we encode it into base32.

```php
<?php

//We use OpenSSL to generate our random string.
$secret = openssl_random_pseudo_bytes(40);

//We encode it using the dependency of the library
use Base32\Base32;

$encoded_secret = Base32::encode($secret);
```

# Example 2

In this example, we will generate a string using [our random string generator](https://github.com/Spomky-Labs/defuse-generator).

```php
<?php

//Please verify that the Spomky-Labs/defuse-generator library is correctly installed
use Security\DefuseGenerator;

$encoded_secret = DefuseGenerator::getRandomString(40, "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567");
```
