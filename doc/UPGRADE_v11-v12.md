# Upgrade from v11 to v12

This document provides guidance for upgrading from OTPHP v11.x to v12.0.

## Breaking Changes

### Readonly Classes - Mutable Methods Removed

**Impact:** HIGH - Affects all code using setter methods

In v12.0, all OTP classes will become `readonly`, and all mutable `set*()` methods will be removed. In v11.4, these methods are deprecated and replaced with immutable `with*()` methods that return a new instance instead of modifying the existing one.

#### Deprecated Methods

All `set*()` methods are deprecated in v11.4 and will be removed in v12.0:

**OTPInterface:**
- `setSecret(string $secret): void` → `withSecret(string $secret): self`
- `setDigits(int $digits): void` → `withDigits(int $digits): self`
- `setDigest(string $digest): void` → `withDigest(string $digest): self`
- `setLabel(string $label): void` → `withLabel(string $label): self`
- `setIssuer(string $issuer): void` → `withIssuer(string $issuer): self`
- `setIssuerIncludedAsParameter(bool $issuer): void` → `withIssuerIncludedAsParameter(bool $issuer): self`
- `setParameter(string $parameter, mixed $value): void` → `withParameter(string $parameter, mixed $value): self`

**HOTPInterface:**
- `setCounter(int $counter): void` → `withCounter(int $counter): self`

**TOTPInterface:**
- `setPeriod(int $period): void` → `withPeriod(int $period): self`
- `setEpoch(int $epoch): void` → `withEpoch(int $epoch): self`

#### Migration Path

**v11.3 and earlier (Mutable):**
```php
use OTPHP\TOTP;
use OTPHP\InternalClock;

$totp = TOTP::generate(new InternalClock());
$totp->setLabel('alice@example.com');
$totp->setIssuer('My Service');
$totp->setDigits(8);
// $totp is modified in place
```

**v11.4 (Transitional - Deprecated warnings):**
```php
use OTPHP\TOTP;
use OTPHP\InternalClock;

// Old way still works but triggers deprecation warnings
$totp = TOTP::generate(new InternalClock());
$totp->setLabel('alice@example.com'); // Deprecated warning

// New immutable way
$totp = TOTP::generate(new InternalClock())
    ->withLabel('alice@example.com')
    ->withIssuer('My Service')
    ->withDigits(8);
// Each with*() method returns a new instance
```

**v12.0 (Required - Readonly classes):**
```php
use OTPHP\TOTP;
use OTPHP\InternalClock;

// Only immutable methods available
$totp = TOTP::generate(new InternalClock())
    ->withLabel('alice@example.com')
    ->withIssuer('My Service')
    ->withDigits(8);
```

#### Common Migration Patterns

**Pattern 1: Simple property updates**
```php
// Before (v11.3)
$otp->setSecret('NEWSECRET');
$otp->setDigits(8);

// After (v11.4+)
$otp = $otp->withSecret('NEWSECRET')
    ->withDigits(8);
```

**Pattern 2: Conditional updates**
```php
// Before (v11.3)
if ($useCustomLabel) {
    $otp->setLabel($customLabel);
}

// After (v11.4+)
if ($useCustomLabel) {
    $otp = $otp->withLabel($customLabel);
}
```

**Pattern 3: HOTP counter increment**
```php
// Before (v11.3)
$hotp->setCounter($hotp->getCounter() + 1);

// After (v11.4+)
$hotp = $hotp->withCounter($hotp->getCounter() + 1);
```

**Pattern 4: Method chaining initialization**
```php
// Before (v11.3) - Required multiple statements
$totp = TOTP::createFromSecret('secret', new InternalClock());
$totp->setLabel('user@example.com');
$totp->setIssuer('MyApp');
$totp->setParameter('image', 'https://example.com/logo.png');

// After (v11.4+) - Clean method chaining
$totp = TOTP::createFromSecret('secret', new InternalClock())
    ->withLabel('user@example.com')
    ->withIssuer('MyApp')
    ->withParameter('image', 'https://example.com/logo.png');
```

#### Benefits of Immutable API

1. **Thread Safety:** Immutable objects are inherently thread-safe
2. **Predictability:** No unexpected mutations from other code
3. **Better Testing:** Easier to reason about and test
4. **Modern PHP:** Aligns with PHP 8.2+ readonly properties

#### Preparing for v12.0

To prepare your codebase:

1. **Find all setter usage:**
   ```bash
   grep -r "->set[A-Z]" --include="*.php" | grep -v vendor
   ```

2. **Replace with immutable alternatives:**
   - Replace `->setX()` with `= $obj->withX()`
   - Remember to reassign the result to a variable
   - Chain multiple `with*()` calls for cleaner code

3. **Update tests to check immutability:**
   ```php
   $original = TOTP::generate(new InternalClock());
   $modified = $original->withLabel('test');

   // These should be different instances
   assert($original !== $modified);
   assert($original->getLabel() === null);
   assert($modified->getLabel() === 'test');
   ```

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

### PSR-20 Clock Changes
- **v11.3.0:** Clock parameter introduced as optional
- **v11.4.0:** Deprecation warnings added when Clock is not provided
- **v12.0.0:** Clock parameter becomes mandatory (planned)

### Readonly Classes Migration
- **v11.4.0:** Immutable `with*()` methods added, `set*()` methods deprecated
- **v12.0.0:** Classes become `readonly`, all `set*()` methods removed (planned)

## Need Help?

If you encounter issues during migration:

1. Check the [documentation](https://github.com/Spomky-Labs/otphp/tree/11.4.x/doc)
2. Search [existing issues](https://github.com/Spomky-Labs/otphp/issues)
3. Create a [new issue](https://github.com/Spomky-Labs/otphp/issues/new) with details about your migration challenge
