# Extending all classes #

## Description of classes ##

This library has three abstract classes:

* OTP: the base of all OTP classes
* TOTP: Time Based OTP
* HOTP: Counter Based OTP

## OTP Class ##

The all classes must implement the following methods:

* ```public functon getLabel();```
* ```public functon getIssuer();```
* ```public functon isIssuerIncludedAsParameter();```
* ```public functon getDigits();```
* ```public functon getDigest();```

If you wand to extend

* TOTP Class, you must also implement ```public functon getInterval();```.
* HOTP Class, you must also implement ```public functon getCounter();```.

The following class is a possible implementation of the TOTP Class:

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
            $this->label = $label;
            return $this;
        }

        public function getLabel()
        {
            return $this->label;
        }

        public function setIssuer($issuer)
        {
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
            $this->digits = $digits;
            return $this;
        }

        public function getDigits()
        {
            return $this->digits;
        }

        public function setDigest($digest)
        {
            $this->digest = $digest;
            return $this;
        }

        public function getDigest()
        {
            return $this->digest;
        }

        public function setInterval($interval)
        {
            if( !is_numeric($interval) || $interval < 1 ) {
                throw new \Exception("Interval must be at least 1.");
            }
            $this->interval = $interval;
            return $this;
        }

        public function getInterval()
        {
            return $this->interval;
        }
    }

This this class, you can easily create a TOTP object:

    <?php
    use MyProject\TOTP;

    $totp = new TOTP;
    $totp->setSecret('JDDK4U6G3BJLEZ7Y');

Your object is ready to use.
You can also change all options:

    $totp->setLabel('foo@bar.baz')
         ->setIssuer('My Project')
         ->setDigits(4)
         ->setDigest('sha512')
         ->setInterval(60);
