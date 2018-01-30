<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

interface CodeGeneratorInterface
{
    /**
     * Generate a new authentication code an send it to the user.
     *
     * @param TwoFactorInterface $user
     */
    public function generateAndSend(TwoFactorInterface $user): void;
}
