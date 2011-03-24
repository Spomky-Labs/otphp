<?php
namespace OTPHP {

class OTP {
    protected $secret;
    protected $digest;
    protected $digits;

    public function __construct($s, $opt = Array()) {
      $this->digits = isset($opt['digits']) ? $opt['digits'] : 6;
      $this->digest = isset($opt['digest']) ? $opt['digest'] : 'sha1';
      $this->secret = $s;
    }

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
