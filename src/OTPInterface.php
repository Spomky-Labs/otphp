<?php

declare(strict_types=1);

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
     * @return string Return the OTP at the specified timestamp
     */
    public function at(int $timestamp): string;

    /**
     * Verify that the OTP is valid with the specified input.
     * If no input is provided, the input is set to a default value or false is returned.
     */
    public function verify(string $otp, ?int $input = null, ?int $window = null): bool;

    /**
     * @return string The secret of the OTP
     */
    public function getSecret(): string;

    /**
     * @param string $label The label of the OTP
     */
    public function setLabel(string $label): void;

    /**
     * @return string|null The label of the OTP
     */
    public function getLabel(): ?string;

    /**
     * @return string|null The issuer
     */
    public function getIssuer(): ?string;

    public function setIssuer(string $issuer): void;

    /**
     * @return bool If true, the issuer will be added as a parameter in the provisioning URI
     */
    public function isIssuerIncludedAsParameter(): bool;

    public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter): void;

    /**
     * @return int Number of digits in the OTP
     */
    public function getDigits(): int;

    /**
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest(): string;

    /**
     * @return null|mixed
     */
    public function getParameter(string $parameter);

    public function hasParameter(string $parameter): bool;

    public function getParameters(): array;

    public function setParameter(string $parameter, $value): void;

    /**
     * @return string Get the provisioning URI
     */
    public function getProvisioningUri(): string;

    /**
     * @param string $uri         The Uri of the QRCode generator with all parameters. By default the Googgle Chart API is used. This Uri MUST contain a placeholder that will be replaced by the method.
     * @param string $placeholder The placeholder to be replaced in the QR Code generator URI. Default value is {PROVISIONING_URI}.
     *
     * @return string Get the provisioning URI
     */
    public function getQrCodeUri(string $uri = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={PROVISIONING_URI}', string $placeholder = '{PROVISIONING_URI}'): string;
}
