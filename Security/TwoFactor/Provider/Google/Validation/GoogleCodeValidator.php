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

    /**
     * Construct a validator for Google Authenticator code.
     *
     * @param GoogleAuthenticator $authenticator
     */
    public function __construct(GoogleAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param int                $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $this->authenticator->checkCode($user, $code);
    }
}
