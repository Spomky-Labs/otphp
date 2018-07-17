<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class GoogleAuthenticator implements GoogleAuthenticatorInterface
{
    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $issuer;

    public function __construct(?string $server, ?string $issuer)
    {
        $this->server = $server;
        $this->issuer = $issuer;
    }

    public function checkCode(TwoFactorInterface $user, string $code): bool
    {
        $totp = $this->createTotp($user);

        return $totp->verify($code);
    }

    public function getUrl(TwoFactorInterface $user): string
    {
        $totp = $this->createTotp($user);

        return $totp->getQrCodeUri();
    }

    public function getQRContent(TwoFactorInterface $user): string
    {
        $totp = $this->createTotp($user);

        return $totp->getProvisioningUri();
    }

    public function generateSecret(): string
    {
        return trim(Base32::encodeUpper(random_bytes(32)), '=');
    }

    private function createTotp(TwoFactorInterface $user): TOTP
    {
        $totp = TOTP::create($user->getGoogleAuthenticatorSecret());
        $userAndHost = $user->getGoogleAuthenticatorUsername();
        if ($this->server) {
            $userAndHost .= '@'.$this->server;
        }

        $totp->setLabel($userAndHost);
        if ($this->issuer) {
            $totp->setIssuer($this->issuer);
        }

        return $totp;
    }
}
