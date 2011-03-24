<?php
namespace OTPHP {
  class OTP {
    protected $secret;

    public function __construct($s, $opt = Array()) {
      $this->secret = $s;

    }

    public function byteSecret() {
      return \Base32::decode($this->secret);
    }

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
