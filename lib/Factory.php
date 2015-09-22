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
        self::checkData($parsed_url);

        switch ($parsed_url['host']) {
            case 'totp':
                $otp = self::createTOTP();
                break;
            case 'hotp':
                $otp = self::createHOTP();
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported "%s" OTP type', $parsed_url['host']));
        }

        parse_str($parsed_url['query'], $query);
        if (null === $query) {
            throw new \InvalidArgumentException('Invalid OTP: invalid parameters');
        }

        foreach ($query as $key=>$value) {
            if ('issuer' === $key) {
                $otp->setIssuer($value);
            } else {
                $otp->setParameter($key, $value);
            }
        }
        list($issuer, $label) = explode(':', rawurldecode(substr($parsed_url['path'], 1)));
        $otp->setLabel($label);
        if (!empty($otp->getIssuer())) {
            if ($issuer !== $otp->getIssuer()) {
                throw new \InvalidArgumentException('Invalid OTP: invalid issuer in parameter');
            }
            $otp->setIssuerIncludedAsParameter(true);
        }
        $otp->setIssuer($issuer);

        return $otp;
    }

    /**
     * @param array|bool $data
     */
    private static function checkData($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
        }
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
            }
        }
        if ($data['scheme'] !== 'otpauth') {
            throw new \InvalidArgumentException('Not a valid OTP provisioning URI');
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
