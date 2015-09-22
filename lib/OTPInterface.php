<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
     * @param string $secret
     *
     * @return $this
     */
    public function setSecret($secret);

    /**
     * @return string The label of the OTP
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string The issuer
     */
    public function getIssuer();

    /**
     * @param string $issuer
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setIssuer($issuer);

    /**
     * @return bool If true, the issuer will be added as a parameter in the provisioning URI
     */
    public function isIssuerIncludedAsParameter();

    /**
     * @param bool $issuer_included_as_parameter
     *
     * @return $this
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter);

    /**
     * @return int Number of digits in the OTP
     */
    public function getDigits();

    /**
     * @param int $digits
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setDigits($digits);

    /**
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest();

    /**
     * @param string $digest
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setDigest($digest);

    /**
     * @return string The URL of an image associated to the provisioning URI
     */
    public function getImage();

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image);

    /**
     * @param string $parameter
     *
     * @return null|mixed
     */
    public function getParameter($parameter);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param string $parameter
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter($parameter, $value);

    /**
     * @param bool $google_compatible If true (default), will produce provisioning URI compatible with Google Authenticator. Only applicable if algorithm="sha1", period=30 and digits=6.
     *
     * @return string Get the provisioning URI
     */
    public function getProvisioningUri($google_compatible = true);
}
