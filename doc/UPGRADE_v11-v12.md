# Upgrade from v11 to v12

This document provides guidance for upgrading from OTPHP v11.x to v12.0.

## Breaking Changes

### PSR-20 Clock Parameter Becomes Mandatory

**Impact:** HIGH - Affects all TOTP usage

In v12.0, the `ClockInterface` parameter will become mandatory for TOTP operations. Currently in v11.4+, this parameter is optional and triggers a deprecation warning when not provided.

#### Methods Affected

The following methods will require a PSR-20 Clock implementation in v12.0:

- `TOTP::__construct(string $secret, ClockInterface $clock)`
- `TOTP::generate(ClockInterface $clock, ?int $secretSize = null)`
- `TOTP::create(..., ClockInterface $clock, ...)`
- `TOTP::createFromSecret(string $secret, ClockInterface $clock)`
- `Factory::loadFromProvisioningUri(string $uri, ClockInterface $clock)`

#### Migration Path

**v11.4 (Current - Deprecated):**
```php
use OTPHP\TOTP;

// This triggers a deprecation warning in v11.4
$totp = TOTP::generate();
```

**v12.0 (Required):**
```php
use OTPHP\TOTP;
use OTPHP\InternalClock;

// You must provide a Clock implementation
$totp = TOTP::generate(new InternalClock());
```

#### Recommended Clock Implementations

1. **For production use** - Use a PSR-20 compliant clock library:
   ```php
   composer require symfony/clock
   ```
   ```php
   use Symfony\Component\Clock\NativeClock;
   use OTPHP\TOTP;

   $clock = new NativeClock();
   $totp = TOTP::generate($clock);
   ```

2. **For simple use cases** - Use the built-in `InternalClock`:
   ```php
   use OTPHP\InternalClock;
   use OTPHP\TOTP;

   $totp = TOTP::generate(new InternalClock());
   ```

3. **For testing** - Use a fixed/mock clock:
   ```php
   use Symfony\Component\Clock\MockClock;
   use OTPHP\TOTP;

   $clock = new MockClock('2024-01-01 12:00:00');
   $totp = TOTP::generate($clock);
   ```

#### Factory Usage

**v11.4 (Deprecated):**
```php
use OTPHP\Factory;

$otp = Factory::loadFromProvisioningUri($uri);
```

**v12.0 (Required):**
```php
use OTPHP\Factory;
use OTPHP\InternalClock;

$otp = Factory::loadFromProvisioningUri($uri, new InternalClock());
```

## Preparing for v12.0 in v11.4

To prepare your codebase for v12.0 while still using v11.4:

1. **Update all TOTP instances to provide a Clock:**
   ```bash
   # Search for TOTP usage without clock parameter
   grep -r "TOTP::generate()" --include="*.php"
   grep -r "TOTP::create(" --include="*.php"
   grep -r "new TOTP(" --include="*.php"
   grep -r "Factory::loadFromProvisioningUri(" --include="*.php"
   ```

2. **Add Clock parameter to all matches:**
   - Replace `TOTP::generate()` with `TOTP::generate(new InternalClock())`
   - Replace `new TOTP($secret)` with `new TOTP($secret, new InternalClock())`
   - Add clock parameter to `Factory::loadFromProvisioningUri()`

3. **Run your tests to ensure no deprecation warnings:**
   ```bash
   vendor/bin/phpunit
   ```

4. **Check for deprecation warnings in logs:**
   All instances should be updated when you no longer see:
   ```
   The parameter "$clock" will become mandatory in 12.0.0.
   Please set a valid PSR Clock implementation instead of "null".
   ```

## Benefits of Using PSR-20 Clock

Providing a Clock implementation has several benefits:

1. **Testability:** Easily mock time in tests
2. **Consistency:** Ensure consistent time source across your application
3. **Flexibility:** Switch between different clock implementations (UTC, local, mock, etc.)
4. **Best Practice:** Following PSR-20 standard promotes better architecture

## Timeline

- **v11.3.0:** Clock parameter introduced as optional
- **v11.4.0:** Deprecation warnings added when Clock is not provided
- **v12.0.0:** Clock parameter becomes mandatory (planned)

## Need Help?

If you encounter issues during migration:

1. Check the [documentation](https://github.com/Spomky-Labs/otphp/tree/11.4.x/doc)
2. Search [existing issues](https://github.com/Spomky-Labs/otphp/issues)
3. Create a [new issue](https://github.com/Spomky-Labs/otphp/issues/new) with details about your migration challenge
