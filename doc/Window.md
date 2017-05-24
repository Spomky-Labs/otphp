# The window parameter

The method `verify` has a `window` parameter. By default, its value is `null`. This means that the OTP will be tested at the exact counter/timestamp.

## Window and HOTP

If the value is an integer, the method will try all OTP from `counter` to `counter + window`.
For example, if the `counter` is `1000` and the window `10`, the OTP tested are within `1000` and `1010`.

```php
<?php
$hotp->verify('123456', 999); // Will return false
$hotp->verify('123456', 999, 10); // Will return true (1000 is tested)
```

## Window and TOTP

The window of timestamps goes from `timestamp - window * period` to `timestamp + window * period`.
For example, if the `window` is `5`, the period `30` and the timestamp `1476822000`, the OTP tested are within `1476821850` (`1476822000 - 5 * 30`) and `1476822150` (`1476822000 + 5 * 30`).

```php
$totp->at(1000); // e.g. will return '123456'
$totp->verify('123456'); // Will return true
// 30 seconds later
$totp->verify('123456'); // Will return false
$totp->verify('123456', null, 1); // Will return true during the next period
```
