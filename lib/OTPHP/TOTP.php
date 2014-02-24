<?php

namespace OTPHP;
/**
 * TOTP - One time password generator 
 * 
 * The TOTP class allow for the generation 
 * and verification of one-time password using 
 * the TOTP specified algorithm.
 *
 * This class is meant to be compatible with 
 * Google Authenticator
 *
 * This class was originally ported from the rotp
 * ruby library available at https://github.com/mdp/rotp
 */

use OTPHP\HOTP;


class TOTP extends HOTP
{
    /**
     * The interval in seconds for a one-time password timeframe
     * Defaults to 30
     * @var integer
     */
    protected $interval;

    public function __construct($secret, $interval = 30, $digest = 'sha1', $digit = 6) {
        $this->setInterval($interval);
        parent::__construct($secret, $digest, $digit);
    }

    /**
     *  Get the password for a specific timestamp value 
     *
     *  @param integer $timestamp the timestamp which is timecoded and 
     *  used to seed the hmac hash function.
     *  @return integer the One Time Password
     */
    public function at($timestamp) {
        return $this->generateOTP($this->timecode($timestamp));
    }

    /**
     *  Get the password for the current timestamp value 
     *
     *  @return integer the current One Time Password
     */
    public function now() {
        return $this->generateOTP($this->timecode(time()));
    }

    /**
     * Verify if a password is valid for a specific counter value
     *
     * @param integer $otp the one-time password 
     * @param integer $timestamp the timestamp for the a given time, defaults to current time.
     * @return  bool true if the counter is valid, false otherwise
     */
    public function verify($otp, $timestamp = null) {
        if($timestamp === null)
            $timestamp = time();
        return ($otp === $this->at($timestamp));
    }

    /**
     * Returns the uri for a specific secret for totp method.
     * Can be encoded as a image for simple configuration in 
     * Google Authenticator.
     *
     * @param string $name the name of the account / profile
     * @return string the uri for the hmac secret
     */
    public function provisioningURI($name) {
        return "otpauth://totp/".urlencode($name)."?secret={$this->getSecret()}";
    }

    /**
     * Transform a timestamp in a counter based on specified internal
     *
     * @param integer $timestamp
     * @return integer the timecode
     */
    protected function timecode($timestamp) {
        return (int)( (((int)$timestamp * 1000) / ($this->getInterval() * 1000)));
    }

    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    public function getInterval() {
        return $this->interval;
    }
}
