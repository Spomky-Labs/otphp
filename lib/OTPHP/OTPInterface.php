<?php

namespace OTPHP;

interface OTPInterface
{
    /**
     * @return integer
     */
    public function at($counter);

    /**
     * @param |null $counter
     *
     * @return boolean
     */
    public function verify($otp, $counter);
    public function getSecret();
    public function getLabel();
    public function getIssuer();
    public function isIssuerIncludedAsParameter();
    public function getDigits();
    public function getDigest();
    public function getProvisioningUri();
}
