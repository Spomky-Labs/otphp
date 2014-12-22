<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface CodeValidatorInterface
{

    /**
     * Validates the code, which was entered by the user
     *
     * @param  \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user
     * @param  integer                                                $code
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code);
}
