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
class OTP {
    /**
     * The base32 encoded secret key
     * @var string
     */
    public $secret;

    /**
     * The algorithm used for the hmac hash function
     * @var string
     */
    public $digest;

    /**
     * The number of digits in the one-time password
     * @var integer
     */ 
    public $digits;

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
    public function __construct($secret, $opt = Array()) {
      $this->digits = isset($opt['digits']) ? $opt['digits'] : 6;
      $this->digest = isset($opt['digest']) ? $opt['digest'] : 'sha1';
      $this->secret = $secret;
    }

    /**
     * Generate a one-time password
     *
     * @param integer $input : number used to seed the hmac hash function.
     * This number is usually a counter (HOTP) or calculated based on the current
     * timestamp (see TOTP class).
     * @return integer the one-time password 
     */
    public function generateOTP($input) {
      $hash = hash_hmac($this->digest, $this->intToBytestring($input), $this->byteSecret());
      foreach(str_split($hash, 2) as $hex) { // stupid PHP has bin2hex but no hex2bin WTF
        $hmac[] = hexdec($hex);
      }
      $offset = $hmac[19] & 0xf;
      $code = ($hmac[$offset+0] & 0x7F) << 24 |
        ($hmac[$offset + 1] & 0xFF) << 16 |
        ($hmac[$offset + 2] & 0xFF) << 8 |
        ($hmac[$offset + 3] & 0xFF);
      return $code % pow(10, $this->digits);
    }

    /**
     * Returns the binary value of the base32 encoded secret
     * @access private
     * This method should be private but was left public for
     * phpunit tests to work.
     * @return binary secret key
     */
    public function byteSecret() {
      return \Base32::decode($this->secret);
    }

    /**
     * Turns an integer in a OATH bytestring
     * @param integer $int
     * @access private
     * @return string bytestring
     */
    public function intToBytestring($int) {
      $result = Array();
      while($int != 0) {
        $result[] = chr($int & 0xFF);
        $int >>= 8;
      }
      return str_pad(join(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }
  }
}
