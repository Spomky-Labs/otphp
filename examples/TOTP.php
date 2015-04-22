<?php

namespace MyProject;

use OTPHP\TOTP as BaseTOTP;

class TOTP extends BaseTOTP
{
    /**
     * @var string|null
     */
    protected $secret = null;
    /**
     * @var string|null
     */
    protected $issuer = null;
    /**
     * @var bool
     */
    protected $issuer_included_as_parameter = false;
    /**
     * @var string|null
     */
    protected $label = null;
    /**
     * @var string
     */
    protected $digest = 'sha1';
    /**
     * @var int
     */
    protected $digits = 6;
    /**
     * @var int
     */
    protected $interval = 30;

    /**
     * @param $secret
     *
     * @return self
     */
    public function setSecret($secret)
    {
        //You must check that the secret is a valid Base32 string
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param $label
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \InvalidArgumentException("Label must not contain a semi-colon.");
        }
        $this->label = $label;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $issuer
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \InvalidArgumentException("Issuer must not contain a semi-colon.");
        }
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return bool
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    /**
     * @param $issuer_included_as_parameter
     *
     * @return self
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;

        return $this;
    }

    /**
     * @param $digits
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setDigits($digits)
    {
        if (!is_integer($digits) || $digits < 1) {
            throw new \InvalidArgumentException("Digits must be at least 1.");
        }
        $this->digits = $digits;

        return $this;
    }

    /**
     * @return int
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @param $digest
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setDigest($digest)
    {
        if (!in_array($digest, array('md5', 'sha1', 'sha256', 'sha512'))) {
            throw new \InvalidArgumentException("'$digest' digest is not supported.");
        }
        $this->digest = $digest;

        return $this;
    }

    /**
     * @return string
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @param $interval
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setInterval($interval)
    {
        if (!is_integer($interval) || $interval < 1) {
            throw new \InvalidArgumentException("Interval must be at least 1.");
        }
        $this->interval = $interval;

        return $this;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param $value
     *
     * @return bool
     */
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
