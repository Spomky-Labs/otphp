<?php
namespace OTPHP {
  class TOTP extends OTP {
    public $interval;

    public function __construct($s, $opt = Array()) {
      $this->interval = isset($opt['interval']) ? $opt['interval'] : 30;
      parent::__construct($s, $opt);
    }

    public function at($timestamp) {
      return $this->generateOTP($this->timecode($timestamp));
    }

    public function now() {
      return $this->generateOTP($this->timecode(time()));
    }

    public function verify($otp, $timestamp = null) {
      if($timestamp === null)
        $timestamp = time();
      return ($otp == $this->at($timestamp));
    }

    public function provisioning_uri($name) {
      return "otpauth://totp/".urlencode($name)."?secret={$this->secret}";
    }

    protected function timecode($timestamp) {
      return (int)( (((int)$timestamp * 1000) / ($this->interval * 1000)));
    }
  }

}
