<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;

class TrustedComputerManager implements TrustedComputerManagerInterface
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    public function addTrustedComputer($user, string $trustedToken, \DateTime $validUntil)
    {
        if ($user instanceof TrustedComputerInterface) {
            $user->addTrustedComputer($trustedToken, $validUntil);
            $this->persister->persist($user);
        }
    }

    public function isTrustedComputer($user, string $token): bool
    {
        if ($user instanceof TrustedComputerInterface) {
            return $user->isTrustedComputer($token);
        }

        return false;
    }
}
