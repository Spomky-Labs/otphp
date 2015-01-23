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

    /**
     * @param string $secret
     */
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

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \Exception("Label must not containt a semi-colon.");
        }
        $this->label = $label;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $issuer
     */
    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \Exception("Issuer must not containt a semi-colon.");
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

    /**
     * @param boolean $issuer_included_as_parameter
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;

        return $this;
    }

    /**
     * @param integer $digits
     */
    public function setDigits($digits)
    {
        if (!is_integer($digits) || $digits < 1) {
            throw new \Exception("Digits must be at least 1.");
        }
        $this->digits = $digits;

        return $this;
    }

    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @param string $digest
     */
    public function setDigest($digest)
    {
        if (!in_array($digest, array('md5', 'sha1', 'sha256', 'sha512'))) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;

        return $this;
    }

    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @param integer $interval
     */
    public function setInterval($interval)
    {
        if (!is_integer($interval) || $interval < 1) {
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
