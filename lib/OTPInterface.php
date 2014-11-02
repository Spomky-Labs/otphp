<?php

namespace OTPHP;

interface OTPInterface
{
    /**
     * @param integer $input
     *
     * @return integer Return the OTP at the specified input
     */
    public function at($input);

    /**
     * Verify that the OTP is valid with the specified input
     *
     * @param string       $otp
     * @param integer|null $input
     * @param integer|null $window
     *
     * @return boolean
     */
    public function verify($otp, $input, $window = null);

    /**
     * @return string The secret of the OTP
     */
    public function getSecret();

    /**
     * @return string The label of the OTP
     */
    public function getLabel();

    /**
     * @return string The issuer
     */
    public function getIssuer();

    /**
     * @return boolean If true, the issuer will be added as a parameter in the provisioning URI
     */
    public function isIssuerIncludedAsParameter();

    /**
     * @return integer Number of digits in the OTP
     */
    public function getDigits();

    /**
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest();

    /**
     * @return string Get the provisioneng URI
     */
    public function getProvisioningUri();
}
