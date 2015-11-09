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

use Base32\Base32;

abstract class OTP implements OTPInterface
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $issuer = null;

    /**
     * @var string|null
     */
    private $label = null;

    /**
     * @var bool
     */
    private $issuer_included_as_parameter = false;

    public function __construct()
    {
        $this->setDigest('sha1')
            ->setDigits(6);
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
     * @return bool Return true is it must be included as parameter, else false
     */
    protected function issuerAsParameter()
    {
        if (null !== $this->getIssuer() && $this->isIssuerIncludedAsParameter() === true) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $options
     * @param bool  $google_compatible
     */
    protected function filterOptions(array &$options, $google_compatible)
    {
        if (true === $google_compatible) {
            foreach (['algorithm' => 'sha1', 'period' => 30, 'digits' => 6] as $key => $default) {
                if (isset($options[$key]) && $default === $options[$key]) {
                    unset($options[$key]);
                }
            }
        }

        ksort($options);
    }

    /**
     * @param string $type
     * @param array  $options
     * @param bool   $google_compatible
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function generateURI($type, array $options = [], $google_compatible)
    {
        if (empty($this->getLabel())) {
            throw new \InvalidArgumentException('No label defined.');
        }

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
     * {@inheritdoc}
     */
    public function at($input)
    {
        return $this->generateOTP($input);
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    /**
     * {@inheritdoc}
     */
    public function setSecret($secret)
    {
        return $this->setParameter('secret', $secret);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        if ($this->hasSemicolon($label)) {
            throw new \InvalidArgumentException('Label must not contain a semi-colon.');
        }

        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * {@inheritdoc}
     */
    public function setIssuer($issuer)
    {
        if ($this->hasSemicolon($issuer)) {
            throw new \InvalidArgumentException('Issuer must not contain a semi-colon.');
        }

        $this->issuer = $issuer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDigits()
    {
        return $this->getParameter('digits');
    }

    /**
     * {@inheritdoc}
     */
    public function setDigits($digits)
    {
        if (!is_numeric($digits) || $digits < 1) {
            throw new \InvalidArgumentException('Digits must be at least 1.');
        }

        return $this->setParameter('digits', $digits);
    }

    /**
     * {@inheritdoc}
     */
    public function getDigest()
    {
        return $this->getParameter('algorithm');
    }

    /**
     * {@inheritdoc}
     */
    public function setDigest($digest)
    {
        if (!in_array($digest, hash_algos())) {
            throw new \InvalidArgumentException("'$digest' digest is not supported.");
        }

        return $this->setParameter('algorithm', $digest);
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($image)
    {
        return $this->setParameter('image', $image);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return $this->getParameter('image');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($parameter)
    {
        if (array_key_exists($parameter, $this->parameters)) {
            return $this->parameters[$parameter];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($parameter, $value)
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }

    private function hasSemicolon($value)
    {
        $semicolons = [':', '%3A', '%3a'];
        foreach ($semicolons as $semicolon) {
            if (false !== strpos($value, $semicolon)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     *
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
