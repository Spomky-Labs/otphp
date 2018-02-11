<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TrustedComputerManager implements TrustedComputerManagerInterface
{
    private const DEFAULT_TOKEN_VERSION = 0;

    /**
     * @var TrustedComputerTokenStorage
     */
    private $trustedTokenStorage;

    public function __construct(TrustedComputerTokenStorage $trustedTokenStorage)
    {
        $this->trustedTokenStorage = $trustedTokenStorage;
    }

    public function addTrustedComputer($user, string $firewallName): void
    {
        if (!($user instanceof UserInterface)) {
            return;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);
        $this->trustedTokenStorage->addTrustedToken($username, $firewallName, $version);
    }

    public function isTrustedComputer($user, string $firewallName): bool
    {
        if (!($user instanceof UserInterface)) {
            return false;
        }

        $username = $user->getUsername();
        $version = $this->getTrustedTokenVersion($user);

        return $this->trustedTokenStorage->hasTrustedToken($username, $firewallName, $version);
    }

    private function getTrustedTokenVersion($user): int
    {
        if ($user instanceof TrustedComputerInterface) {
            return $user->getTrustedTokenVersion();
        }

        return self::DEFAULT_TOKEN_VERSION;
    }
}
