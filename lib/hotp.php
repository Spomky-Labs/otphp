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
   * HOTP - One time password generator 
   * 
   * The HOTP class allow for the generation 
   * and verification of one-time password using 
   * the HOTP specified algorithm.
   *
   * This class is meant to be compatible with 
   * Google Authenticator
   *
   * This class was originally ported from the rotp
   * ruby library available at https://github.com/mdp/rotp
   */
  class HOTP extends OTP {
    /**
     *  Get the password for a specific counter value
     *  @param integer $count the counter which is used to
     *  seed the hmac hash function.
     *  @return integer the One Time Password
     */
    public function at($count) {
      return $this->generateOTP($count);
    }


    /**
     * Verify if a password is valid for a specific counter value
     *
     * @param integer $otp the one-time password 
     * @param integer $counter the counter value
     * @return  bool true if the counter is valid, false otherwise
     */
    public function verify($otp, $counter) {
      return ($otp == $this->at($counter));
    }

    /**
     * Returns the uri for a specific secret for hotp method.
     * Can be encoded as a image for simple configuration in 
     * Google Authenticator.
     *
     * @param string $name the name of the account / profile
     * @param integer $initial_count the initial counter 
     * @return string the uri for the hmac secret
     */
    public function provisioning_uri($name, $initial_count) {
      return "otpauth://hotp/".urlencode($name)."?secret={$this->secret}&counter=$initial_count";
    }
  }

}
