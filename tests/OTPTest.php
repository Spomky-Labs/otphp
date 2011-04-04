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

require_once dirname(__FILE__).'/../lib/otphp.php';

class TestOTP extends PHPUnit_Framework_TestCase {

  public function test_it_decodes_the_secret() {
    $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals("H\306\256S\306\330R\262g\370", $o->byteSecret());
  }

  public function test_it_turns_an_int_into_bytestring() {
    $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals("\000\000\000\000\000\000\000\000", $o->intToBytestring(0));
    $this->assertEquals("\000\000\000\000\000\000\000\001", $o->intToBytestring(1));
    $this->assertEquals("\000\000\000\000\000\000\001\364", $o->intToBytestring(500));
    $this->assertEquals("\000\000\000\000\000\000\005\334", $o->intToBytestring(1500));
  }

  public function test_it_generate_otp() {
    $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals(855783, $o->generateOTP(0));
    $this->assertEquals(549607, $o->generateOTP(500));
    $this->assertEquals(654666, $o->generateOTP(1500));
  }
}
