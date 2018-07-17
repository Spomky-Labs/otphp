<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Model;

interface PreferredProviderInterface
{
    /**
     * Return the alias of the preferred two-factor provider (if chosen by the user).
     *
     * @return string|null
     */
    public function getPreferredTwoFactorProvider(): ?string;
}
