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

    /**
     * @param PersisterInterface $persister
     */
    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    /**
     * Add a trusted computer token for a user.
     *
     * @param mixed     $user
     * @param string    $token
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer($user, $token, \DateTime $validUntil)
    {
        if ($user instanceof TrustedComputerInterface) {
            $user->addTrustedComputer($token, $validUntil);
            $this->persister->persist($user);
        }
    }

    /**
     * Validate a trusted computer token for a user.
     *
     * @param mixed  $user
     * @param string $token
     *
     * @return bool
     */
    public function isTrustedComputer($user, $token)
    {
        if ($user instanceof TrustedComputerInterface) {
            return $user->isTrustedComputer($token);
        }

        return false;
    }
}
