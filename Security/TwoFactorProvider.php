<?php
namespace Scheb\TwoFactorBundle\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        /** @var TwoFactorToken $token */
        if (!$this->supports($token)) {
            return null;
        }
        if (!$token->getCredentials()) {
            return $token;
        }

        return $token->getAuthenticatedToken();
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken;
    }
}
