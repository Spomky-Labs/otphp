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

use Assert\Assertion;
use ParagonIE\ConstantTime\Base32;

abstract class OTP implements OTPInterface
{
    use ParameterTrait;

    /**
     * OTP constructor.
     *
     * @param string|null $label
     * @param string|null $secret
     * @param string      $digest
     * @param int         $digits
     */
    public function __construct($label, $secret, $digest, $digits)
    {
        $this->setLabel($label);
        $this->setSecret($secret);
        $this->setDigest($digest);
        $this->setDigits($digits);
    }

    /**
     * {@inheritdoc}
     */
    public function getQrCodeUri($uri = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl={PROVISIONING_URI}', $placeholder = '{PROVISIONING_URI}')
    {
        $provisioning_uri = urlencode($this->getProvisioningUri());

        return str_replace($placeholder, $provisioning_uri, $uri);
    }

    /**
     * @param int $input
     *
     * @return string The OTP at the specified input
     */
    protected function generateOTP($input)
    {
        $hash = hash_hmac($this->getDigest(), $this->intToByteString($input), $this->getDecodedSecret());
        $hmac = [];
        foreach (str_split($hash, 2) as $hex) {
            $hmac[] = hexdec($hex);
        }
        $offset = $hmac[count($hmac) - 1] & 0xF;
        $code = ($hmac[$offset + 0] & 0x7F) << 24 | ($hmac[$offset + 1] & 0xFF) << 16 | ($hmac[$offset + 2] & 0xFF) << 8 | ($hmac[$offset + 3] & 0xFF);
        $otp = $code % pow(10, $this->getDigits());

        return str_pad((string) $otp, $this->getDigits(), '0', STR_PAD_LEFT);
    }

    /**
     * {@inheritdoc}
     */
    public function at($input)
    {
        return $this->generateOTP($input);
    }

    /**
     * @param array $options
     */
    protected function filterOptions(array &$options)
    {
        foreach (['algorithm' => 'sha1', 'period' => 30, 'digits' => 6] as $key => $default) {
            if (isset($options[$key]) && $default === $options[$key]) {
                unset($options[$key]);
            }
        }

        ksort($options);
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return string
     */
    protected function generateURI($type, array $options)
    {
        $label = $this->getLabel();
        Assertion::string($label, 'The label is not set.');
        Assertion::false($this->hasColon($label), 'Label must not contain a colon.');
        $options = array_merge($options, $this->getParameters());
        $this->filterOptions($options);
        $params = str_replace(['+', '%7E'], ['%20', '~'], http_build_query($options));

        return sprintf('otpauth://%s/%s?%s', $type, rawurlencode((null !== $this->getIssuer() ? $this->getIssuer().':' : '').$label), $params);
    }

    /**
     * @return string
     */
    private function getDecodedSecret()
    {
        $secret = Base32::decodeUpper($this->getSecret());

        return $secret;
    }

    /**
     * @param int $int
     *
     * @return string
     */
    private function intToByteString($int)
    {
        $result = [];
        while (0 !== $int) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }

        return str_pad(implode(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    /**
     * @param string $safe
     * @param string $user
     *
     * @return bool
     */
    protected function compareOTP($safe, $user)
    {
        return hash_equals($safe, $user);
    }
}
