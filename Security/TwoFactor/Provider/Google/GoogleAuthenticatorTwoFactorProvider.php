<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class GoogleAuthenticatorTwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var CodeValidatorInterface
     */
    private $authenticator;

    public function __construct(CodeValidatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        // Check if user can do email authentication
        $user = $context->getUser();

        return $user instanceof TwoFactorInterface && $user->getGoogleAuthenticatorSecret();
    }

    public function validateAuthenticationCode(AuthenticationContextInterface $context, string $authenticationCode): bool
    {
        return $this->authenticator->checkCode($context->getUser(), $authenticationCode);
    }
}
