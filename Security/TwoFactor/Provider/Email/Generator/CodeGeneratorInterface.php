<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
