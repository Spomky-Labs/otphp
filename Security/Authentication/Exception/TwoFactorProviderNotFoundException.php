<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorProviderNotFoundException extends AuthenticationException
{
    public const MESSAGE_KEY = 'Two-factor provider not found.';

    private $provider;

    public function getMessageKey()
    {
        return self::MESSAGE_KEY;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    public function serialize()
    {
        return serialize([
            $this->provider,
            parent::serialize(),
        ]);
    }

    public function unserialize($str)
    {
        list($this->provider, $parentData) = unserialize($str);
        parent::unserialize($parentData);
    }

    public function getMessageData()
    {
        return ['{{ provider }}' => $this->provider];
    }
}
