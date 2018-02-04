<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Voter;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TwoFactorInProgressVoter implements VoterInterface
{
    const IS_AUTHENTICATED_2FA_IN_PROGRESS = 'IS_AUTHENTICATED_2FA_IN_PROGRESS';

    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        if (!($token instanceof TwoFactorToken)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($attribute === self::IS_AUTHENTICATED_2FA_IN_PROGRESS) {
                return VoterInterface::ACCESS_GRANTED;
            }
            if ($attribute === AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
