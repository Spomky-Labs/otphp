<?php

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
