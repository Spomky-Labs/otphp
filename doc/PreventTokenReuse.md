# Preventing TOTP Token Reuse

The method `verifyOtpWithWindow` has a `after` parameter. By default, its value is `null`. A timestamp can be given to only accept tokens which are generated after the period of the timestamp.
This can be used to prevent token reuse by keeping track of the last time a uses's OTP was verified successfully. To get the exact timestamp of the code which was used the method `verifyOtpWithWindow` returns the timestamp 
of the verified code if its valid and null otherwise.

```php
# Load the timestamp of the last successful otp login from storage e.g. a database
$lastSuccessfulOtpLogin = getLastSucessfulOtpLoginFromStorage();

$lastOtpAt = $totp->verifyOtpWithWindow('123456', null, null, $lastSuccessfulOtpLogin); # will return the timestamp 
if ($lastOtpAt != null) {
    # otp is valid
    saveLastSuccessfulOtpLoginInStorage($lastOtpAt);
} else {
    # otp is invalid
}

# Attempting to try the same code again inside the 30s period 
$lastOtpAt = $totp->verifyOtpWithWindow('123456', null, null, $lastSuccessfulOtpLogin); # will return null
```
