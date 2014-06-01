<?php

namespace OTPHP;

use Base32\Base32;

abstract class OTP implements OTPInterface
{
    protected $secret;
    protected $issuer;
    protected $issuer_included_as_parameter;
    protected $label;
    protected $digest;
    protected $digits;

    /**
     * @param string $secret
     * @param string $digest
     * @param integer $digits
     * @param string $issuer
     * @param string $label
     * @param boolean $issuer_included_as_parameter
     */
    public function __construct($secret, $digest = 'sha1', $digits = 6, $issuer = null, $label = null, $issuer_included_as_parameter = true)
    {
        $this->setSecret($secret);
        $this->setLabel($label);
        $this->setIssuer($issuer);
        $this->setIssuerIncludedAsParameter($issuer_included_as_parameter);
        $this->setDigits($digits);
        $this->setDigest($digest);
    }

    /**
     * @param integer $input
     */
    protected function generateOTP($input)
    {
        $hash = hash_hmac($this->getDigest(), $this->intToBytestring($input), $this->getDecodedSecret());
        $hmac = array();
        foreach(str_split($hash, 2) as $hex) {
            $hmac[] = hexdec($hex);
        }
        $offset = $hmac[19] & 0xf;
        $code = ($hmac[$offset+0] & 0x7F) << 24 |
            ($hmac[$offset + 1] & 0xFF) << 16 |
            ($hmac[$offset + 2] & 0xFF) << 8 |
            ($hmac[$offset + 3] & 0xFF);
        return $code % pow(10, $this->getDigits());
    }

    /**
     * @param string $type
     */
    protected function generateURI($type, $opt = array())
    {
        if( $this->getLabel() === null ) {
            throw new \Exception("No label defined.");
        }
        $opt['algorithm'] = $this->getDigest();
        $opt['digits'] = $this->getDigits();
        $opt['secret'] = $this->getSecret();
        if( $this->getIssuer() !== null && $this->isIssuerIncludedAsParameter() === true ) {
            $opt['issuer'] = $this->getIssuer();
        }

        ksort($opt);

        $params = str_replace(
            array('+', '%7E'), 
            array('%20', '~'), 
            http_build_query($opt)
        );
        return "otpauth://$type/".rawurlencode(($this->getIssuer()!==null?$this->getIssuer().':':'').$this->getLabel())."?$params";
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function getDecodedSecret()
    {
        $secret = Base32::decode($this->getSecret());
        return $secret;
    }

    /**
     * @param integer $int
     * 
     * @return string
     */
    private function intToBytestring($int)
    {
        $result = array();
        while($int != 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }
        return str_pad(implode(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    /**
     * {@inheritdoc}
     */
    public function at($counter)
    {
        return $this->generateOTP($counter);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $counter)
    {
        return ($otp == $this->at($counter));
    }

    /**
     * @param string $secret
     *
     * @return OTP The object itself for chained calls
     */
    public function setSecret($secret)
    {
        $secret = Base32::encode(Base32::decode($secret));
        if (empty($secret)) {
            throw new \Exception("The secret must be a valid Base32 encoded string.");
        }
        $this->secret = $secret;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $label
     *
     * @return OTP The object itself for chained calls
     */
    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \Exception("Label must not containt a semi-colon.");
        }
        $this->label = $label;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $issuer
     *
     * @return OTP The object itself for chained calls
     */
    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \Exception("Issuer must not containt a semi-colon.");
        }
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    /**
     * @param boolean $issuer_included_as_parameter
     *
     * @return OTP The object itself for chained calls
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param integer $digits
     *
     * @return OTP The object itself for chained calls
     */
    public function setDigits($digits)
    {
        if( $digits < 1 ) {
            throw new \Exception("Digits must be at least 1.");
        }
        $this->digits = $digits;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @param string $digest
     *
     * @return OTP The object itself for chained calls
     */
    public function setDigest($digest)
    {
        if( !in_array($digest, array('md5', 'sha1', 'sha256', 'sha512')) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @param string $value
     *
     * @return boolean
     */
    protected function hasSemicolon($value)
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
