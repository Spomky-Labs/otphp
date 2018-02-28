<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TrustedDeviceManager implements TrustedDeviceManagerInterface
{
    private const DEFAULT_TOKEN_VERSION = 0;

    /**
     * @var TrustedDeviceTokenStorage
     */
    private $trustedTokenStorage;

    /**
     * @var bool
     */
    private $useTrustedOption;

    public function __construct(TrustedDeviceTokenStorage $trustedTokenStorage, bool $useTrustedOption)
    {
        $this->trustedTokenStorage = $trustedTokenStorage;
        $this->useTrustedOption = $useTrustedOption;
    }

    public function addTrustedDevice($user, string $firewallName): void
    {
        if (!$this->useTrustedOption) {
            return;
        }
        if (!($user instanceof UserInterface)) {
            return;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);
        $this->trustedTokenStorage->addTrustedToken($username, $firewallName, $version);
    }

    public function isTrustedDevice($user, string $firewallName): bool
    {
        if (!$this->useTrustedOption) {
            return false;
        }
        if (!($user instanceof UserInterface)) {
            return false;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);

        return $this->trustedTokenStorage->hasTrustedToken($username, $firewallName, $version);
    }

    private function getTrustedTokenVersion($user): int
    {
        if ($user instanceof TrustedDeviceInterface) {
            return $user->getTrustedTokenVersion();
        }

        return self::DEFAULT_TOKEN_VERSION;
    }
}
