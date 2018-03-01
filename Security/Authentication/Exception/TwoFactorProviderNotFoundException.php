<?php

namespace Scheb\TwoFactorBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorProviderNotFoundException extends AuthenticationException
{
    private $provider;

    public function getMessageKey()
    {
        return 'Two-factor provider not found.';
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
        return serialize(array(
            $this->provider,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list($this->provider, $parentData) = unserialize($str);
        parent::unserialize($parentData);
    }

    public function getMessageData()
    {
        return array('{{ provider }}' => $this->provider);
    }
}
