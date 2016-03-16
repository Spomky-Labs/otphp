<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Base32\Base32;

abstract class OTP implements OTPInterface
{
    use ParameterTrait;

    /**
     * OTP constructor.
     *
     * @param string $label
     * @param string $secret
     * @param string $digest
     * @param int    $digits
     */
    public function __construct($label, $secret, $digest, $digits)
    {
        $this->setLabel($label);
        $this->setSecret($secret);
        $this->setDigest($digest);
        $this->setDigits($digits);
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
        $code = ($hmac[$offset + 0] & 0x7F) << 24 |
            ($hmac[$offset + 1] & 0xFF) << 16 |
            ($hmac[$offset + 2] & 0xFF) << 8 |
            ($hmac[$offset + 3] & 0xFF);

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
     * @param bool  $google_compatible
     */
    protected function filterOptions(array &$options, $google_compatible)
    {
        if (true === $google_compatible) {
            $this->cleanOptions($options);
        }

        ksort($options);
    }

    /**
     * @param array $options
     */
    private function cleanOptions(array &$options)
    {
        foreach (['algorithm' => 'sha1', 'period' => 30, 'digits' => 6] as $key => $default) {
            if (isset($options[$key]) && $default === $options[$key]) {
                unset($options[$key]);
            }
        }
    }

    /**
     * @param string $type
     * @param array  $options
     * @param bool   $google_compatible
     *
     * @return string
     */
    protected function generateURI($type, array $options, $google_compatible)
    {
        $options = array_merge($options, $this->getParameters());
        if ($this->issuerAsParameter()) {
            $options['issuer'] = $this->getIssuer();
        }

        $this->filterOptions($options, $google_compatible);

        $params = str_replace(
            ['+', '%7E'],
            ['%20', '~'],
            http_build_query($options)
        );

        return sprintf(
            'otpauth://%s/%s?%s',
            $type,
            rawurlencode((null !== $this->getIssuer() ? $this->getIssuer().':' : '').$this->getLabel()),
            $params
        );
    }

    /**
     * @return string
     */
    private function getDecodedSecret()
    {
        $secret = Base32::decode($this->getSecret());

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
        if (function_exists('hash_equals')) {
            return hash_equals($safe, $user);
        }
        $safeLen = strlen($safe);
        $userLen = strlen($user);

        if ($userLen !== $safeLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safe[$i]) ^ ord($user[$i]));
        }

        return $result === 0;
    }
}
