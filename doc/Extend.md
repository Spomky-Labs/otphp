# Extending all classes

## Description of classes

This library has three abstract classes:

* OTP: the base of all OTP classes
* TOTP: Time Based OTP
* HOTP: Counter Based OTP

## OTP Class

The all classes must implement the following methods:

* ```public functon getLabel();```
* ```public functon getIssuer();```
* ```public functon isIssuerIncludedAsParameter();```
* ```public functon getDigits();```
* ```public functon getDigest();```

If you wand to extend

* TOTP Class, you must also implement ```public functon getInterval();```.
* HOTP Class, you must also implement ```public functon getCounter();``` and ```protected functon updateCounter($counter);```.

## Implementation

### TOTP Class

The following class is a possible implementation of the TOTP Class:

```php
<?php

namespace MyProject;

use OTPHP\TOTP as BaseTOTP;

class TOTP extends BaseTOTP
{
    protected $secret = null;
    protected $issuer = null;
    protected $issuer_included_as_parameter = false;
    protected $label = null;
    protected $digest = 'sha1';
    protected $digits = 6;
    protected $interval = 30;

    public function setSecret($secret)
    {
        //You must check that the secret is a valid Base32 string
        $this->secret = $secret;
        return $this;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \Exception("Label must not contain a semi-colon.");
        }
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \Exception("Issuer must not contain a semi-colon.");
        }
        $this->issuer = $issuer;
        return $this;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
        return $this;
    }

    public function setDigits($digits)
    {
        if( !is_numeric($digits) || $digits < 1 ) {
            throw new \Exception("Digits must be at least 1.");
        }
        $this->digits = $digits;
        return $this;
    }

    public function getDigits()
    {
        return $this->digits;
    }

    public function setDigest($digest)
    {
        if( !in_array($digest, array('md5', 'sha1', 'sha256', 'sha512')) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;
        return $this;
    }

    public function getDigest()
    {
        return $this->digest;
    }

    public function setInterval($interval)
    {
        if( !is_integer($interval) || $interval < 1 ) {
            throw new \Exception("Interval must be at least 1.");
        }
        $this->interval = $interval;
        return $this;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    private function hasSemicolon($value)
    {
        $semicolons = array(':', '%3A', '%3a');
        foreach ($semicolons as $semicolon) {
            if (false !== strpos($value, $semicolon)) {
                return true;
            }
        }

        return false;
    }
}
```

This this class, you can easily create a TOTP object:

```php
<?php
use MyProject\TOTP;

$totp = new TOTP;
$totp->setSecret('JDDK4U6G3BJLEZ7Y');
```

Your object is ready to use.
You can also change all options:

```php
$totp->setLabel('foo@bar.baz')
     ->setIssuer('My Project')
     ->setDigits(4)
     ->setDigest('sha512')
     ->setInterval(60);
```


### HOTP Class

The following class is a possible implementation of the HOTP Class:

```php
<?php

namespace MyProject;

use OTPHP\HOTP as BaseHOTP;

class HOTP extends BaseHOTP
{
    protected $secret = null;
    protected $issuer = null;
    protected $issuer_included_as_parameter = false;
    protected $label = null;
    protected $digest = 'sha1';
    protected $digits = 6;
    protected $counter = 0;

    public function setSecret($secret)
    {
        //You must check that the secret is a valid Base32 string
        $this->secret = $secret;
        return $this;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \Exception("Label must not contain a semi-colon.");
        }
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \Exception("Issuer must not contain a semi-colon.");
        }
        $this->issuer = $issuer;
        return $this;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
        return $this;
    }

    public function setDigits($digits)
    {
        if( !is_numeric($digits) || $digits < 1 ) {
            throw new \Exception("Digits must be at least 1.");
        }
        $this->digits = $digits;
        return $this;
    }

    public function getDigits()
    {
        return $this->digits;
    }

    public function setDigest($digest)
    {
        if( !in_array($digest, array('md5', 'sha1', 'sha256', 'sha512')) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;
        return $this;
    }

    public function getDigest()
    {
        return $this->digest;
    }

    public function setCounter($counter)
    {
        if( !is_integer($counter) || $counter < 0 ) {
            throw new \Exception("Counter must be at least 0.");
        }
        $this->counter = $counter;
        return $this;
    }

    public function getCounter()
    {
        return $this->counter;
    }

    public function updateCounter($counter)
    {
        $this->counter = $counter;
        return $this;
    }

    private function hasSemicolon($value)
    {
        $semicolons = array(':', '%3A', '%3a');
        foreach ($semicolons as $semicolon) {
            if (false !== strpos($value, $semicolon)) {
                return true;
            }
        }

        return false;
    }
}
```

This this class, you can easily create a HOTP object:

```php
<?php
use MyProject\HOTP;

$hotp = new HOTP;
$hotp->setSecret('JDDK4U6G3BJLEZ7Y');
```

Your object is ready to use.
You can also change all options:

```php
$hotp->setLabel('foo@bar.baz')
     ->setIssuer('My Project')
     ->setDigits(4)
     ->setDigest('sha512')
     ->setCounter(100);
```
