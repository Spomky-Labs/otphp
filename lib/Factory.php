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

class Factory
{
    /**
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     *
     * @return \OTPHP\TOTP|\OTPHP\HOTP
     */
    public static function loadFromProvisioningUri($uri)
    {
        $parsed_url = parse_url($uri);
        if (!is_array($parsed_url)) {
            throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
        }
        self::checkData($parsed_url);

        $otp = self::createOTP($parsed_url['host']);

        self::populateOTP($otp, $parsed_url);

        return $otp;
    }

    /**
     * @param \OTPHP\OTPInterface $otp
     * @param array               $data
     */
    private static function populateParameters(OTPInterface &$otp, array $data)
    {
        foreach ($data['query'] as $key => $value) {
            if ('issuer' === $key) {
                $otp->setIssuer($value);
            } else {
                $otp->setParameter($key, $value);
            }
        }
    }

    /**
     * @param \OTPHP\OTPInterface $otp
     * @param array               $data
     */
    private static function populateOTP(OTPInterface &$otp, array $data)
    {
        self::populateParameters($otp, $data);
        list($issuer, $label) = explode(':', rawurldecode(substr($data['path'], 1)));
        $otp->setLabel($label);

        if (!empty($otp->getIssuer())) {
            if ($issuer !== $otp->getIssuer()) {
                throw new \InvalidArgumentException('Invalid OTP: invalid issuer in parameter');
            }
            $otp->setIssuerIncludedAsParameter(true);
        }
        $otp->setIssuer($issuer);
    }

    /**
     * @param array $data
     */
    private static function checkData(&$data)
    {
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
            }
        }
        if ('otpauth' !== $data['scheme']) {
            throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
        }
        parse_str($data['query'], $data['query']);
    }

    /**
     * @param string $type
     *
     * @return \OTPHP\HOTP|\OTPHP\TOTP
     */
    private static function createOTP($type)
    {
        switch ($type) {
            case 'totp':
                return self::createTOTP();
            case 'hotp':
                return self::createHOTP();
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported "%s" OTP type', $type));
        }
    }

    private static function createTOTP()
    {
        return new TOTP();
    }

    private static function createHOTP()
    {
        return new HOTP();
    }
}
