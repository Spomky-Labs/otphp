<?php

namespace OTPHP;

interface OTPInterface
{
    /**
     * @param int $input
     *
     * @return string Return the OTP at the specified input
     */
    public function at($input);

    /**
     * Verify that the OTP is valid with the specified input.
     *
     * @param string   $otp
     * @param int      $input
     * @param int|null $window
     *
     * @return bool
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
     * @return bool If true, the issuer will be added as a parameter in the provisioning URI
     */
    public function isIssuerIncludedAsParameter();

    /**
     * @return int Number of digits in the OTP
     */
    public function getDigits();

    /**
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest();

    /**
     * @param bool $google_compatible If true (default), will produce provisioning URI compatible with Google Authenticator. Only applicable if algorithm="sha1", period=30 and digits=6.
     *
     * @return string Get the provisioning URI
     */
    public function getProvisioningUri($google_compatible = true);
}
