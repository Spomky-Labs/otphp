# Exception Handling

OTPHP provides a comprehensive exception hierarchy for better error handling and debugging. All OTPHP exceptions extend standard PHP exceptions to maintain backward compatibility while providing more specific error information.

## Exception Hierarchy

All OTPHP exceptions implement the `OTPExceptionInterface` marker interface, allowing you to catch all library-specific exceptions:

```php
try {
    $totp = TOTP::createFromSecret('invalid secret!@#');
} catch (\OTPHP\Exception\OTPExceptionInterface $e) {
    // Catches any OTPHP exception
}
```

### Available Exceptions

#### InvalidParameterException

**Extends:** `InvalidArgumentException`

Thrown when an OTP parameter has an invalid value. This includes validation errors for:
- `secret`: Invalid or empty secret value
- `digits`: Invalid number of digits (must be at least 1)
- `algorithm`: Unsupported hash algorithm
- `period`: Invalid period value (TOTP only, must be at least 1)
- `epoch`: Invalid epoch value (TOTP only, must be >= 0)
- `counter`: Invalid counter value (HOTP only, must be >= 0)
- `secretSize`: Invalid secret size (must be at least 1)
- `timestamp`: Invalid timestamp value
- `leeway`: Invalid leeway value

**Properties:**
- `parameterName` (string): The name of the invalid parameter
- `parameterValue` (mixed): The invalid value that was provided

**Example:**

```php
try {
    $totp = TOTP::create('SECRET');
    $totp->setDigits(0); // Invalid: must be at least 1
} catch (\OTPHP\Exception\InvalidParameterException $e) {
    echo $e->getMessage(); // "Digits must be at least 1."
    echo $e->parameterName; // "digits"
    echo $e->parameterValue; // 0
}
```

#### InvalidLabelException

**Extends:** `InvalidArgumentException`

Thrown when a label or issuer format is invalid according to the Google Authenticator specification. This includes:
- Empty labels or issuers
- Labels/issuers containing colons (`:`, `%3A`, or `%3a`)
- Invalid issuer:account format

**Properties:**
- `labelName` (string): The name of the label field (e.g., 'label', 'issuer')
- `labelValue` (mixed): The invalid value that was provided

**Example:**

```php
try {
    $totp = TOTP::createFromSecret('SECRET');
    $totp->setLabel('user:invalid:format'); // Invalid: multiple colons
} catch (\OTPHP\Exception\InvalidLabelException $e) {
    echo $e->getMessage(); // "Neither issuer nor account name in label may contain a colon."
    echo $e->labelName; // "label"
    echo $e->labelValue; // "user:invalid:format"
}
```

#### InvalidProvisioningUriException

**Extends:** `InvalidArgumentException`

Thrown when a provisioning URI cannot be parsed or is invalid. This occurs when:
- URI scheme is not `otpauth://`
- Required URI components are missing (scheme, host, path, query)
- Secret parameter is missing from query string
- OTP type is unsupported (not `totp` or `hotp`)

**Example:**

```php
try {
    $otp = Factory::loadFromProvisioningUri('invalid-uri');
} catch (\OTPHP\Exception\InvalidProvisioningUriException $e) {
    echo $e->getMessage(); // "Not a valid OTP provisioning URI"
}
```

#### ParameterNotFoundException

**Extends:** `InvalidArgumentException`

Thrown when attempting to access a parameter that doesn't exist in the OTP object.

**Properties:**
- `parameterName` (string): The name of the missing parameter

**Example:**

```php
try {
    $totp = TOTP::createFromSecret('SECRET');
    $value = $totp->getParameter('nonexistent');
} catch (\OTPHP\Exception\ParameterNotFoundException $e) {
    echo $e->getMessage(); // "Parameter \"nonexistent\" does not exist"
    echo $e->parameterName; // "nonexistent"
}
```

#### SecretDecodingException

**Extends:** `RuntimeException`

Thrown when a secret cannot be decoded from Base32 format or when the decoded secret is empty.

**Example:**

```php
try {
    $totp = TOTP::createFromSecret('INVALID!@#$%');
    $totp->now(); // Triggers secret decoding
} catch (\OTPHP\Exception\SecretDecodingException $e) {
    echo $e->getMessage(); // "Unable to decode the secret. Is it correctly base32 encoded?"
}
```

## Backward Compatibility

All custom exceptions extend standard PHP exceptions, ensuring existing code continues to work:

```php
// This still works - catches InvalidParameterException and InvalidLabelException
try {
    $totp = TOTP::create('SECRET');
    $totp->setDigits(-1);
} catch (\InvalidArgumentException $e) {
    // Catches all exceptions extending InvalidArgumentException
}

// This also works - catches SecretDecodingException
try {
    $totp = TOTP::createFromSecret('INVALID!@#');
    $totp->now();
} catch (\RuntimeException $e) {
    // Catches all exceptions extending RuntimeException
}
```

## Best Practices

### Specific Exception Handling

Catch specific exceptions when you need targeted error handling:

```php
try {
    $otp = Factory::loadFromProvisioningUri($uri);
    $isValid = $otp->verify($code);
} catch (\OTPHP\Exception\InvalidProvisioningUriException $e) {
    // Handle URI parsing errors
    log('Invalid provisioning URI: ' . $e->getMessage());
} catch (\OTPHP\Exception\InvalidParameterException $e) {
    // Handle parameter validation errors
    log("Invalid {$e->parameterName}: {$e->getMessage()}");
}
```

### Catch All OTPHP Exceptions

Use the marker interface to catch all library-specific exceptions:

```php
try {
    $totp = TOTP::createFromSecret($_POST['secret']);
    $totp->setLabel($_POST['label']);
    $totp->setIssuer($_POST['issuer']);
} catch (\OTPHP\Exception\OTPExceptionInterface $e) {
    // Handle any OTPHP-specific error
    return response()->json(['error' => $e->getMessage()], 400);
} catch (\Exception $e) {
    // Handle unexpected errors
    log()->error('Unexpected error: ' . $e->getMessage());
    return response()->json(['error' => 'Internal server error'], 500);
}
```

### Access Exception Properties

Use public readonly properties for detailed error information:

```php
try {
    $totp = TOTP::create('SECRET');
    $totp->setDigits($_POST['digits']);
} catch (\OTPHP\Exception\InvalidParameterException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Parameter: {$e->parameterName}\n";
    echo "Invalid value: " . json_encode($e->parameterValue) . "\n";

    // Output:
    // Error: Digits must be at least 1.
    // Parameter: digits
    // Invalid value: 0
}
```

## Migration Guide

No migration is required! The new exception system is fully backward compatible:

- All custom exceptions extend standard PHP exceptions
- Existing `catch` blocks continue to work without changes
- Exception messages remain unchanged
- You can start using specific exception types at your own pace

### Optional: Gradual Migration

You can gradually migrate to more specific exception handling:

```php
// Before (still works)
try {
    $totp = TOTP::createFromSecret($secret);
} catch (\InvalidArgumentException $e) {
    // ...
}

// After (more specific)
try {
    $totp = TOTP::createFromSecret($secret);
} catch (\OTPHP\Exception\InvalidParameterException $e) {
    // Handle parameter errors with access to parameter name/value
} catch (\OTPHP\Exception\SecretDecodingException $e) {
    // Handle Base32 decoding errors
}
```
