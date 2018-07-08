<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;

class JwtTokenEncoder
{
    public const CLAIM_USERNAME = 'usr';
    public const CLAIM_FIREWALL = 'fwl';
    public const CLAIM_VERSION = 'vsn';

    /**
     * @var Sha256
     */
    private $signer;

    /**
     * @var string
     */
    private $applicationSecret;

    public function __construct(string $applicationSecret)
    {
        $this->signer = new Sha256();
        $this->applicationSecret = $applicationSecret;
    }

    public function generateToken(string $username, string $firewallName, int $version, \DateTime $validUntil): Token
    {
        $builder = (new Builder())
            ->setIssuedAt(time())
            ->setExpiration($validUntil->getTimestamp())
            ->set(self::CLAIM_USERNAME, $username)
            ->set(self::CLAIM_FIREWALL, $firewallName)
            ->set(self::CLAIM_VERSION, $version)
            ->sign($this->signer, $this->applicationSecret); // creates a signature using "testing" as key

        return $builder->getToken();
    }

    public function decodeToken(string $token): ?Token
    {
        try {
            $token = (new Parser())->parse($token);
        } catch (\InvalidArgumentException $e) {
            return null; // Could not decode token
        }

        try {
            if (!$token->verify($this->signer, $this->applicationSecret)) {
                return null;
            }
        } catch (\BadMethodCallException $e) {
            return null;
        }

        if ($token->isExpired()) {
            return null;
        }

        return $token;
    }
}
