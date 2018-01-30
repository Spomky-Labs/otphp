<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class GoogleCodeValidator implements CodeValidatorInterface
{
    /**
     * @var GoogleAuthenticator
     */
    private $authenticator;

    public function __construct(GoogleAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function checkCode(TwoFactorInterface $user, string $code): bool
    {
        return $this->authenticator->checkCode($user, $code);
    }
}
