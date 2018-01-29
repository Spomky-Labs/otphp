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

use Assert\Assertion;

/**
 * This class is used to load OTP object from a provisioning Uri.
 */
final class Factory
{
    /**
     * This method is the unique public method of the class.
     * It can load a provisioning Uri and convert it into an OTP object.
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     *
     * @return OTPInterface
     */
    public static function loadFromProvisioningUri(string $uri): OTPInterface
    {
        $parsed_url = parse_url($uri);
        Assertion::isArray($parsed_url, 'Not a valid OTP provisioning URI');
        self::checkData($parsed_url);

        $otp = self::createOTP($parsed_url);

        self::populateOTP($otp, $parsed_url);

        return $otp;
    }

    /**
     * @param OTPInterface $otp
     * @param array        $data
     */
    private static function populateParameters(OTPInterface &$otp, array $data)
    {
        foreach ($data['query'] as $key => $value) {
            $otp->setParameter($key, $value);
        }
    }

    /**
     * @param OTPInterface $otp
     * @param array        $data
     */
    private static function populateOTP(OTPInterface &$otp, array $data)
    {
        self::populateParameters($otp, $data);
        $result = explode(':', rawurldecode(substr($data['path'], 1)));

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
     * @return OTPInterface
     */
    private static function createOTP(array $parsed_url): OTPInterface
    {
        switch ($parsed_url['host']) {
            case 'totp':
                $totp = TOTP::create($parsed_url['query']['secret']);
                $totp->setLabel(self::getLabel($parsed_url['path']));

                return $totp;
            case 'hotp':
                $hotp = HOTP::create($parsed_url['query']['secret']);
                $hotp->setLabel(self::getLabel($parsed_url['path']));

                return $hotp;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported "%s" OTP type', $parsed_url['host']));
        }
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private static function getLabel(string $data): string
    {
        $result = explode(':', rawurldecode(substr($data, 1)));

        return 2 === count($result) ? $result[1] : $result[0];
    }
}
