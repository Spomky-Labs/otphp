<?php
namespace OTPHP {

  class HOTP extends OTP {
    public function at($count) {
      return $this->generateOTP($count);
    }

    public function verify($otp, $counter) {
      return ($otp == $this->at($counter));
    }

    public function provisioning_uri($name, $initial_count) {
      return "otpauth://hotp/".urlencode($name)."?secret={$this->secret}&counter=$initial_count";
    }
  }

}
