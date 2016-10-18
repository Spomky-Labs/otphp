How to generate a secret
========================

The secret used by an OTP is a base32 encoded string.

To create such a secret, you will have to:
* Generate a random string,
* Encode it into base32.

We **highly recommend you use a secure string generator**.

Hereafter some examples known as secured.

Please note that in these examples the size of the secret is 256 bytes (2048 bits).
You can set another size if you want. 128 bytes (1024 bits) should be enough.

# Using Polyfill (The Recommended Solution)

Symfony Polyfill libraries allow you to add some functions only available through PHP extensions or newer PHP versions.
The [`symfony/polyfill-php70`](https://github.com/symfony/polyfill-php70) provides the `random_bytes` function that will generate true random byte strings for you.

Depending on your system, this library will use native PHP 7 functions, the OpenSSL extension, the MCrypt extension or any other known method.

First of all, install the library using composer:

```sh
composer require symfony/polyfill-php70
```

Then generate your random byte string:

```php
<?php

use Base32\Base32;

$secret = random_bytes(256);
$encoded_secret = Base32::encode($secret);
```

# On PHP7 Only

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
