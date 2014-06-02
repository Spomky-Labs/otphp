<?php

namespace OTPHP;

use Base32\Base32;

abstract class OTP implements OTPInterface
{
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
     * @param string $value
     *
     * @return boolean
     */
    private function hasSemicolon($value)
    {
        if ($value === null ) {
            return false;
        }
        if (!is_string($value) ) {
            throw new \Exception('The value is not a string');
        }
        $semicolons = array(':', '%3A', '%3a');
        foreach ($semicolons as $semicolon) {
            if (false !== strpos($value, $semicolon)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param integer $input
     */
    protected function generateOTP($input)
    {
        $this->checkOtpData();

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
    protected function generateURI($type, array $opt = array())
    {
        $this->checkOtpData();
        $this->checkUriData();

        $opt['algorithm'] = $this->getDigest();
        $opt['digits'] = $this->getDigits();
        $opt['secret'] = $this->getSecret();
        if( $this->getIssuer() !== null && $this->isIssuerIncludedAsParameter() === true ) {
            $opt['issuer'] = $this->getIssuer();
        }
        
        return $this->buildURI($type, $opt);
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

    private function checkOtpData() {

        $digits = $this->getDigits();
        if ( !is_numeric($digits) || $digits <0 ) {
            throw new \Exception("Digits must be at least 1.");
        }

        $digest = $this->getDigest();
        if( !in_array($digest, array('md5', 'sha1', 'sha256', 'sha512')) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }

        $secret = $this->getSecret();
        if( empty($secret) || Base32::encode(Base32::decode($secret)) !== $secret ) {
            throw new \Exception("The secret is not a valid Base32 encoded string.");
        }
    }

    private function checkUriData() {

        if ( $this->getLabel() === null ) {
            throw new \Exception("No label defined.");
        }
        if ( $this->hasSemicolon($this->getLabel()) || $this->hasSemicolon($this->getIssuer())) {
            throw new \Exception('The label or the issuer must not contain a semicolon.');
        }
    }

    /**
     * @param string $type Type of OTPAuth
     * @param array  $opt  Options for the OTP generation
     */
    private function buildURI($type, array $opt = array())
    {
        ksort($opt);

        $params = str_replace(
            array('+', '%7E'), 
            array('%20', '~'), 
            http_build_query($opt)
        );
        return "otpauth://$type/".rawurlencode(($this->getIssuer()!==null?$this->getIssuer().':':'').$this->getLabel())."?$params";
    }
}
