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

class TOPTTest extends PHPUnit_Framework_TestCase {

  public function test_it_has_an_interval() {
    $o = new \OTPHP\TOTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals(30,$o->interval);
    $b = new \OTPHP\TOTP('JDDK4U6G3BJLEZ7Y', Array('interval'=>60));
    $this->assertEquals(60,$b->interval);
  }

  public function test_it_gets_the_good_code_at_given_times() {
    $o = new \OTPHP\TOTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals(855783,$o->at(0));
    $this->assertEquals(762124,$o->at(319690800));
    $this->assertEquals(139664,$o->at(1301012137));
  }

  public function test_it_verify_the_code() {
    $o = new \OTPHP\TOTP('JDDK4U6G3BJLEZ7Y');
    $this->assertTrue($o->verify(855783, 0));
    $this->assertTrue($o->verify(762124, 319690800));
    $this->assertTrue($o->verify(139664, 1301012137));
  }

  public function test_it_returns_the_provisioning_uri() {
    $o = new \OTPHP\TOTP('JDDK4U6G3BJLEZ7Y');
    $this->assertEquals("otpauth://totp/name?secret=JDDK4U6G3BJLEZ7Y",
      $o->provisioning_uri('name'));
  }
}
