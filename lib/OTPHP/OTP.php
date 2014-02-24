<?php

namespace OTPHP;

use Base32\Base32;

abstract class OTP
{
    protected $secret;

    protected $digest;

    protected $digits;

    public function __construct($secret, $digest = 'sha1', $digits = 6)
    {

        $this->setSecret($secret);
        $this->setDigits($digits);
        $this->setDigest($digest);
    }

    protected function generateOTP($input)
    {
        $hash = hash_hmac($this->getDigest(), $this->intToBytestring($input), $this->byteSecret());
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

    private function byteSecret()
    {
        return Base32::decode($this->getSecret());
    }

    private function intToBytestring($int)
    {
        $result = array();
        while($int != 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }
        return str_pad(implode(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    public function at($counter)
    {
        return $this->generateOTP($counter);
    }


    public function verify($otp, $counter)
    {
        return ($otp == $this->at($counter));
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    public function getSecret()
    {
        return $this->secret;
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
        if( !in_array($digest, hash_algos()) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;
        return $this;
    }

    public function getDigest()
    {
        return $this->digest;
    }
}
