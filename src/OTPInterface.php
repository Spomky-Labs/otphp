<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
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
     * If no input is provided, the input is set to a default value or false is returned.
     *
     * @param string   $otp
     * @param int|null $input
     * @param int|null $window
     *
     * @return bool
     */
    public function verify($otp, $input = null, $window = null);

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
     * @param string $issuer
     *
     * @throws \InvalidArgumentException
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
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest();

    /**
     * @param string $parameter
     *
     * @return null|mixed
     */
    public function getParameter($parameter);

    /**
     * @param string $parameter
     *
     * @return bool
     */
    public function hasParameter($parameter);

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
     * @return string Get the provisioning URI
     */
    public function getProvisioningUri();

    /**
     * @param string $uri         The Uri of the QRCode generator with all parameters. By default the Googgle Chart API is used. This Uri MUST contain a placeholder that will be replaced by the method.
     * @param string $placeholder The placeholder to be replaced in the QR Code generator URI. Default value is {PROVISIONING_URI}.
     *
     * @return string Get the provisioning URI
     */
    public function getQrCodeUri($uri = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={PROVISIONING_URI}', $placeholder = '{PROVISIONING_URI}');
}
