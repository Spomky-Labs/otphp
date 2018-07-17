<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Lcobucci\JWT\Token;

class TrustedDeviceToken
{
    /**
     * @var Token
     */
    private $jwtToken;

    public function __construct(Token $jwtToken)
    {
        $this->jwtToken = $jwtToken;
    }

    public function authenticatesRealm(string $username, string $firewallName): bool
    {
        return $this->jwtToken->getClaim(JwtTokenEncoder::CLAIM_USERNAME, false) === $username
            && $this->jwtToken->getClaim(JwtTokenEncoder::CLAIM_FIREWALL, false) === $firewallName;
    }

    public function versionMatches(int $version): bool
    {
        return $this->jwtToken->getClaim(JwtTokenEncoder::CLAIM_VERSION, false) === $version;
    }

    public function serialize(): string
    {
        return (string) $this->jwtToken;
    }
}
