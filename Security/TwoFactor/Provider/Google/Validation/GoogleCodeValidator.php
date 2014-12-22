<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class GoogleCodeValidator implements CodeValidatorInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator
     */
    private $authenticator;

    /**
     * Construct a validator for Google Authenticator code
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator $authenticator
     */
    public function __construct(GoogleAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Validates the code, which was entered by the user
     *
     * @param  \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user
     * @param  integer                                                $code
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $this->authenticator->checkCode($user, $code);
    }
}
