<?php

namespace Spomky\OTPHP;

use Spomky\OTPHP\OTP;

class TestOTP extends PHPUnit_Framework_TestCase
{
    public function test_it_decodes_the_secret()
    {
        $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals("H\306\256S\306\330R\262g\370", $o->byteSecret());
    }

    public function test_it_turns_an_int_into_bytestring()
    {
        $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals("\000\000\000\000\000\000\000\000", $o->intToBytestring(0));
        $this->assertEquals("\000\000\000\000\000\000\000\001", $o->intToBytestring(1));
        $this->assertEquals("\000\000\000\000\000\000\001\364", $o->intToBytestring(500));
        $this->assertEquals("\000\000\000\000\000\000\005\334", $o->intToBytestring(1500));
    }

    public function test_it_generate_otp()
    {
        $o = new \OTPHP\OTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals(855783, $o->generateOTP(0));
        $this->assertEquals(549607, $o->generateOTP(500));
        $this->assertEquals(654666, $o->generateOTP(1500));
    }
}
