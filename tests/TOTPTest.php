<?php

use OTPHP\TOTP;

class TOPTTest extends PHPUnit_Framework_TestCase
{
    public function test_it_has_an_interval() {
        $o = new TOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals(30,$o->interval);
        $b = new TOTP('JDDK4U6G3BJLEZ7Y', Array('interval'=>60));
        $this->assertEquals(60,$b->interval);
    }

    public function test_it_gets_the_good_code_at_given_times() {
        $o = new TOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals(855783,$o->at(0));
        $this->assertEquals(762124,$o->at(319690800));
        $this->assertEquals(139664,$o->at(1301012137));
    }

    public function test_it_verify_the_code() {
        $o = new TOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertTrue($o->verify(855783, 0));
        $this->assertTrue($o->verify(762124, 319690800));
        $this->assertTrue($o->verify(139664, 1301012137));
    }

    public function test_it_returns_the_provisioning_uri() {
        $o = new TOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals("otpauth://totp/name?secret=JDDK4U6G3BJLEZ7Y",
            $o->provisioning_uri('name'));
    }
}
