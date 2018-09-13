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

final class Factory
{
    /**
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     *
     * @return \OTPHP\TOTPInterface|\OTPHP\HOTPInterface
     */
    public static function loadFromProvisioningUri($uri)
    {
        $parsed_url = parse_url($uri);
        Assertion::isArray($parsed_url, 'Not a valid OTP provisioning URI');
        self::checkData($parsed_url);

        $otp = self::createOTP($parsed_url);

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
            $otp->setParameter($key, $value);
        }
    }

    /**
     * @param \OTPHP\OTPInterface $otp
     * @param array               $data
     */
    private static function populateOTP(OTPInterface &$otp, array $data)
    {
        self::populateParameters($otp, $data);
        $result = explode(':', rawurldecode(mb_substr($data['path'], 1, null, '8bit')));

        if (2 > count($result)) {
            $otp->setIssuerIncludedAsParameter(false);

            return;
        }

        if (!empty($otp->getIssuer())) {
            Assertion::eq($result[0], $otp->getIssuer(), 'Invalid OTP: invalid issuer in parameter');
            $otp->setIssuerIncludedAsParameter(true);
        }
        $otp->setIssuer($result[0]);
    }

    /**
     * @param array $data
     */
    private static function checkData(array &$data)
    {
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            Assertion::keyExists($data, $key, 'Not a valid OTP provisioning URI');
        }
        Assertion::eq('otpauth', $data['scheme'], 'Not a valid OTP provisioning URI');
        parse_str($data['query'], $data['query']);
        Assertion::keyExists($data['query'], 'secret', 'Not a valid OTP provisioning URI');
    }

    /**
     * @param array $parsed_url
     *
     * @return \OTPHP\HOTPInterface|\OTPHP\TOTPInterface
     */
    private static function createOTP(array $parsed_url)
    {
        switch ($parsed_url['host']) {
            case 'totp':
                return new TOTP(self::getLabel($parsed_url['path']), $parsed_url['query']['secret']);
            case 'hotp':
                return new HOTP(self::getLabel($parsed_url['path']), $parsed_url['query']['secret']);
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported "%s" OTP type', $parsed_url['host']));
        }
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private static function getLabel($data)
    {
        $result = explode(':', rawurldecode(mb_substr($data, 1, null, '8bit')));

        return 2 === count($result) ? $result[1] : $result[0];
    }
}
