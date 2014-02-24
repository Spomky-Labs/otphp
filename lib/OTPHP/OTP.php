<?php

namespace OTPHP;
/**
 * One Time Password Generator 
 * 
 * The OTP class allow the generation of one-time
 * password that is described in rfc 4xxx.
 * 
 * This is class is meant to be compatible with 
 * Google Authenticator.
 *
 * This class was originally ported from the rotp
 * ruby library available at https://github.com/mdp/rotp
 */

use OTPHP\Base32;

abstract class OTP {
    /**
     * The base32 encoded secret key
     * @var string
     */
    protected $secret;

    /**
     * The algorithm used for the hmac hash function
     * @var string
     */
    protected $digest;

    /**
     * The number of digits in the one-time password
     * @var integer
     */ 
    protected $digits;

    /**
     * Constructor for the OTP class
     * @param string $secret the secret key
     * @param array $opt options array can contain the
     * following keys :
     *   @param integer digits : the number of digits in the one time password
     *   Currently Google Authenticator only support 6. Defaults to 6.
     *   @param string digest : the algorithm used for the hmac hash function
     *   Google Authenticator only support sha1. Defaults to sha1
     *
     * @return new OTP class.
     */
    public function __construct($secret, $digest = 'sha1', $digits = 6) {

        $this->setSecret($secret);
        $this->setDigits($digits);
        $this->setDigest($digest);
    }

    /**
     * Generate a one-time password
     *
     * @param integer $input : number used to seed the hmac hash function.
     * This number is usually a counter (HOTP) or calculated based on the current
     * timestamp (see TOTP class).
     * @return integer the one-time password 
     */
    protected function generateOTP($input) {
        $hash = hash_hmac($this->getDigest(), $this->intToBytestring($input), $this->byteSecret());
        foreach(str_split($hash, 2) as $hex) { // stupid PHP has bin2hex but no hex2bin WTF
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
     * Returns the binary value of the base32 encoded secret
     * @return binary secret key
     */
    private function byteSecret() {
        return Base32::decode($this->getSecret());
    }

    /**
     * Turns an integer in a OATH bytestring
     * @param integer $int
     * @return string bytestring
     */
    private function intToBytestring($int) {
        $result = array();
        while($int != 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }
        return str_pad(join(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    public function setSecret($secret) {
        $this->secret = $secret;
        return $this;
    }

    public function getSecret() {
        return $this->secret;
    }

    public function setDigits($digits) {
        $this->digits = $digits;
        return $this;
    }

    public function getDigits() {
        return $this->digits;
    }

    public function setDigest($digest) {
        if( !in_array($digest, hash_algos()) ) {
            throw new \Exception("'$digest' digest is not supported.");
        }
        $this->digest = $digest;
        return $this;
    }

    public function getDigest() {
        return $this->digest;
    }
}
