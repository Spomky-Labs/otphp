# Threat model

This file steers the [OSS Scanner](https://github.com/anthropics/oss-scanner). Vulnerabilities found by other means
are reported as described in [SECURITY.md](../SECURITY.md).

## What this project does

`spomky-labs/otphp` is a PHP library, without framework dependency, for one-time passwords: HOTP (RFC 4226, counter
based) and TOTP (RFC 6238, time based), compatible with Google Authenticator and similar applications. An application
uses it to:

- generate a secret (`HOTP::generate()`, `TOTP::generate()`) and the `otpauth://` provisioning URI that the user
  scans as a QR code (`getProvisioningUri()`, `getQrCodeUri()`);
- verify the code the user types when signing in (`HOTP::verify()`, `TOTP::verify()`);
- load an OTP object back from a provisioning URI, either one it stored itself or one issued by another provider
  (`Factory::loadFromProvisioningUri()`).

The library keeps no state between requests. The application stores the secret (or the whole provisioning URI) and,
for HOTP, the counter after each successful verification.

## Where untrusted input enters

The attacker is anyone who can submit a code to the application, or who can get the application to load a
provisioning URI they produced.

- The code passed to `verify()`. Any string can reach it: wrong length, non-digits, empty, multibyte.
- The provisioning URI passed to `Factory::loadFromProvisioningUri()`. It is parsed by `Url::fromString()` (PHP
  `parse_url()` and `parse_str()`, so query values can also be arrays) and every query key is then applied with
  `setParameter()`: `algorithm`, `digits`, `period`, `epoch`, `counter`, `issuer`, `label` and any custom key. The
  label and issuer also come from the path (`issuer:account`).
- The label and the issuer when the application derives them from user data (account name, email address). They end
  up in the provisioning URI that the user's authenticator application imports.

What is trusted:

- The secret held by the application, how it is stored, and the secret size it asks for.
- The values the application sets through the API: digest, digits, period, epoch, counter, the `window` or `leeway`
  passed to `verify()`, the timestamp or counter passed to `verify()` and `at()`, the PSR-20 clock, and the QR code
  generator URI and placeholder passed to `getQrCodeUri()`.
- The choices made by whoever supplies a provisioning URI. That party also supplies the secret, so a weak value it
  picks (one digit, a very long period) is not a vulnerability. The robustness of the parsing is in scope.

## Security properties that must hold

- `at()` and `now()` follow RFC 4226 and RFC 6238: the code depends on the secret and on the counter or time step
  only, and matches the test vectors of both RFCs.
- `verify()` returns `true` only for the exact code of an accepted counter or time step, and compares it in constant
  time (`OTP::compareOTP()`, `hash_equals()`). It returns `false` for any other string and does not throw on a
  malformed code.
- HOTP: only the counters from the given counter (or the current one) to that counter plus `window` are accepted, a
  counter lower than the current one is rejected, and after a success the counter moves past the accepted value, so
  the same object does not accept the code again.
- TOTP: only the time steps of `timestamp - leeway`, `timestamp` and `timestamp + leeway` are accepted, and the leeway
  must be lower than the period.
- Every way of setting a parameter (`create*()`, `with*()`, `set*()`, `setParameter()`, `withParameter()`, the
  factory) applies the same validation (`OTP::getParameterMap()` and its overrides in `HOTP` and `TOTP`): a digest
  from `hash_hmac_algos()` of at least 19 bytes, 1 to 10 digits, a period of at least 1, an epoch and a counter of at
  least 0, a non-empty label and issuer checked against the colon rules of the Google Authenticator label format.
- `Factory::loadFromProvisioningUri()` either returns an object that satisfies the properties above or throws
  `InvalidProvisioningUriException`. No URI key reaches an internal property of the object: only `label` and `issuer`
  map to their own properties, through their validation; every other key goes to the parameter bag.
- An object returned by the factory throws only the exceptions of `src/Exception` (`OTPExceptionInterface`) from
  `at()`, `now()`, `verify()`, `expiresIn()` or `getProvisioningUri()`.
- The label and the issuer are encoded so that they cannot add, remove or override a parameter of the provisioning
  URI, or change its type or secret.
- Generated secrets come from `random_bytes()` (64 bytes unless the application asks for another size).

## Components that matter most

- `src/OTP.php`: `generateOTP()` (HMAC and dynamic truncation), `compareOTP()`, `setParameter()`,
  `getParameterMap()`, the label and issuer validation, and `generateURI()`.
- `src/HOTP.php`: `verify()` and the window and counter handling.
- `src/TOTP.php`: `verify()`, `at()`, `now()`, `expiresIn()` and the time step computation (`timecode()`).
- `src/Factory.php` and `src/Url.php`: the parsing of provisioning URIs.

Deprecated methods (`create()`, the setters) are in scope as long as they ship.

## How to exercise it

The image has PHP 8.4, the dependencies installed in `vendor/`, and the QA tools of the CI image on the `PATH`.
PHPUnit is not a Composer dependency of this repository: run it as the CI does, with `phpunit-11`.

    composer exec -- phpunit-11 --configuration .ci-tools/phpunit.xml.dist
    composer exec -- phpunit-11 --configuration .ci-tools/phpunit.xml.dist --filter FactoryTest

- `tests/FactoryTest.php` loads provisioning URIs, including hostile ones. A reproducer for the factory goes there.
- `tests/HOTPTest.php` and `tests/TOTPTest.php` cover generation and verification; `TOTPTest` replays the test
  vectors of RFC 6238. A reproducer for verification goes there.
- `tests/ClockMock.php` is a PSR-20 clock whose time a test can set.

## How we rate severity

- Critical: `verify()` accepting a code that was not generated from the secret for an accepted counter or time step,
  in the default configuration (`sha1`, 6 digits, 30 second period, no window); codes that can be predicted without
  the secret; executing code or reading files through the processing of a provisioning URI.
- High: the same in a documented, non-default configuration (another digest, more digits, another period or epoch, a
  window or leeway); a window wider than the one requested; an HOTP code accepted again by the same object after a
  successful verification; a timing difference in `verify()` that reveals the expected code; a generated secret that
  is shorter than requested or not random.
- Medium: a provisioning URI that makes the factory, or the object it returns, throw anything other than the
  documented exceptions, leaves the object in an inconsistent state, or bypasses the label and issuer validation; a
  label or issuer value that adds or overrides a parameter of the generated provisioning URI; a single call on
  attacker input that costs the server disproportionately (unbounded loop or memory).
- Low: defense in depth, misleading exception messages, disclosure of non-sensitive internals, a denial of service
  that needs sustained traffic, a problem that only appears when the application itself passes a value outside the
  range documented by the PHPDoc types.

For calibration, the published advisories were rated as follows:

- GHSA-g7m4-839x-ch6v, unbounded `digits` in a provisioning URI causing an uncaught `DivisionByZeroError` during
  generation: medium.
- GHSA-2jx3-65f3-xr8r, mass assignment of internal properties from the query of a provisioning URI (state corruption,
  uncaught `TypeError`, label validation bypass): medium.

## Anything to leave alone

- Bugs in the dependencies (`paragonie/constant_time_encoding`, `psr/clock`, `symfony/deprecation-contracts`) and in
  the PHP `hash` extension: they are separate projects.
- `tests/`, `doc/`, `.ci-tools/` and `castor.php`.
- What the library leaves to the application: rate limiting and lockout of verification attempts, rejecting a TOTP
  code reused within its validity window, persisting the HOTP counter after a success, storing the secret, and the
  accuracy of the clock.
- Weak but valid values set by the application or by whoever supplies a provisioning URI (one digit, a long period,
  a large window), and interoperability problems with authenticator applications (a digest other than `sha1`,
  `sha256` or `sha512`, more than 6 digits).

## Reports

Include a reproducer written as a PHPUnit test in one of the files above, the parameters it needs when they are not
the defaults, and a patch against the scanned branch. The maintainers port the fix to the maintained branches.
