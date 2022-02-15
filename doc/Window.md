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

The window of TOTP acts as time drift.
If the `window` is `10`, the period `30` and the timestamp `147682209`,
the OTP tested are within `1476821999` (`147682209 - 10`), `147682209` and `1476822219` (`147682209 + 10`).
This includes the previous OTP, but not the next one.

As an example, at the timestamp `147682209` the correct TOTP is `123456`.
But the user device timestamp `1476821995` and the proposed input is the previous one `654321`.

**It is mandatory to have a window lower than the period**.

```php
// Without the window feature, this will fail
$totp->verify('654321'); // returns false

// With a window 5 seconds, this will fail
// because the input is tested with 147682209-5 and 147682209+5 seconds.
// and this does not allow the previous OTP to be used 
$totp->verify('654321', null, 5); // returns false

// With a window 10 seconds, this will succeed
// because the input is tested with 147682209-10 and 147682209+10 seconds.
// and the previous OTP is tested
$totp->verify('654321', null, 10); // returns true
```
