How to generate a secret
========================

The secret used by an OTP is a base32 encoded string.

To create such secret, uou will have to:
* Generate a random string,
* Encode it into base32.

We **we highly recommend you to use a secure string generator**.

Hereafter some examples known as secured.

Please note that in these examples the size of the secret is 256 bytes (2048 bits).
You can set another size if you want. 128 bytes (1024 bits) should be enough.

# On PHP7:

```php
<?php

use Base32\Base32;

$secret = random_bytes(256);
$encoded_secret = Base32::encode($secret);
```

# Using OpenSSL

```php
<?php

use Base32\Base32;

$secret = openssl_random_pseudo_bytes(256);
$encoded_secret = Base32::encode($secret);
```

# Using MCrypt

```php
<?php

use Base32\Base32;

$secret = mcrypt_create_iv(256);
$encoded_secret = Base32::encode($secret);
```
