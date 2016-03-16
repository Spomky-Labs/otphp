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

use Assert\Assertion;
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
     * @param string $secret
     */
    private function setSecret($secret)
    {
        Assertion::string($secret, 'Secret must be a string.');

        $this->setParameter('secret', $secret);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    private function setLabel($label)
    {
        Assertion::string($label, 'Label must be a string.');
        Assertion::false($this->hasSemicolon($label), 'Label must not contain a semi-colon.');

        $this->label = $label;
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
        Assertion::string($issuer, 'Issuer must be a string.');
        Assertion::false($this->hasSemicolon($issuer), 'Issuer must not contain a semi-colon.');

        $this->issuer = $issuer;
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
        Assertion::boolean($issuer_included_as_parameter, 'A boolean is expected.');
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getDigits()
    {
        return $this->getParameter('digits');
    }

    /**
     * @param int $digits
     */
    private function setDigits($digits)
    {
        Assertion::integer($digits, 'Digits must be at least 1.');
        Assertion::greaterThan($digits, 0, 'Digits must be at least 1.');

        $this->setParameter('digits', $digits);
    }

    /**
     * {@inheritdoc}
     */
    public function getDigest()
    {
        return $this->getParameter('algorithm');
    }

    /**
     * @param string $digest
     */
    private function setDigest($digest)
    {
        Assertion::string($digest, 'Digest must be a string.');
        Assertion::inArray($digest, hash_algos(), sprintf('The "%s" digest is not supported.', $digest));

        $this->setParameter('algorithm', $digest);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) {
            return $this->getParameters()[$parameter];
        }

        throw new \InvalidArgumentException(sprintf('Parameter "%s" does not exist', $parameter));
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($parameter, $value)
    {
        Assertion::string($parameter, 'Parameter index must be a string.');

        if ('issuer' === $parameter) {
            $this->setIssuer($value);
        } else {
            $this->parameters[$parameter] = $value;
        }
    }

    /**
     * @param string $value
     *
     * @return bool
     */
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
