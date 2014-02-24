<?php

use Spomky\OTPHP\HOTP;

class HOTPTest extends PHPUnit_Framework_TestCase
{
    public function test_it_gets_the_good_code()
    {
        $o = new HOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals(855783,$o->at(0));
        $this->assertEquals(549607,$o->at(500));
        $this->assertEquals(654666,$o->at(1500));
    }

    public function test_it_verify_the_code()
    {
        $o = new HOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertTrue($o->verify(855783, 0));
        $this->assertTrue($o->verify(549607, 500));
        $this->assertTrue($o->verify(654666, 1500));
    }

    public function test_it_returns_the_provisioning_uri()
    {
        $o = new HOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals("otpauth://hotp/name?secret=JDDK4U6G3BJLEZ7Y&counter=0",
            $o->provisioning_uri('name', 0));
    }
}
