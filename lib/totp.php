<?php
/*
 * Copyright (c) 2011 Le Lag 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace OTPHP {
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
  class TOTP extends OTP {
    /**
     * The interval in seconds for a one-time password timeframe
     * Defaults to 30
     * @var integer
     */
    public $interval;

    public function __construct($s, $opt = Array()) {
      $this->interval = isset($opt['interval']) ? $opt['interval'] : 30;
      parent::__construct($s, $opt);
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
      return ($otp == $this->at($timestamp));
    }

    /**
     * Returns the uri for a specific secret for totp method.
     * Can be encoded as a image for simple configuration in 
     * Google Authenticator.
     *
     * @param string $name the name of the account / profile
     * @return string the uri for the hmac secret
     */
    public function provisioning_uri($name) {
      return "otpauth://totp/".urlencode($name)."?secret={$this->secret}";
    }

    /**
     * Transform a timestamp in a counter based on specified internal
     *
     * @param integer $timestamp
     * @return integer the timecode
     */
    protected function timecode($timestamp) {
      return (int)( (((int)$timestamp * 1000) / ($this->interval * 1000)));
    }
  }

}
