<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class GoogleAuthenticatorTwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var GoogleAuthenticatorInterface
     */
    private $authenticator;

    public function __construct(GoogleAuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        // Check if user can do email authentication
        $user = $context->getUser();

        return $user instanceof TwoFactorInterface && $user->getGoogleAuthenticatorSecret();
    }

    public function validateAuthenticationCode($user, string $authenticationCode): bool
    {
        if (!($user instanceof TwoFactorInterface)) {
            return false;
        }

        return $this->authenticator->checkCode($user, $authenticationCode);
    }
}
