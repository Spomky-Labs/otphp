<?php

namespace OTPHP;

interface OTPInterface
{
    public function at($counter);
    public function verify($otp, $counter);
    public function getSecret();
    public function getLabel();
    public function getIssuer();
    public function isIssuerIncludedAsParameter();
    public function getDigits();
    public function getDigest();
    public function getProvisioningUri();
}
