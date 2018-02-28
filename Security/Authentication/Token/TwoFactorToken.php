<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorToken implements TokenInterface
{
    /**
     * @var TokenInterface
     */
    private $authenticatedToken;

    /**
     * @var string|null
     */
    private $credentials;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var string[]
     */
    private $activeTwoFactorProviders;

    public function __construct(TokenInterface $authenticatedToken, ?string $credentials, string $providerKey, array $activeTwoFactorProviders)
    {
        $this->authenticatedToken = $authenticatedToken;
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        $this->activeTwoFactorProviders = $activeTwoFactorProviders;
    }

    public function getUser()
    {
        return $this->authenticatedToken->getUser();
    }

    public function setUser($user)
    {
        $this->authenticatedToken->setUser($user);
    }

    public function getUsername()
    {
        return $this->authenticatedToken->getUsername();
    }

    public function getRoles()
    {
        return [];
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    public function getAuthenticatedToken(): TokenInterface
    {
        return $this->authenticatedToken;
    }

    public function getActiveTwoFactorProviders(): array
    {
        return $this->activeTwoFactorProviders;
    }

    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    public function isAuthenticated()
    {
        return true;
    }

    public function setAuthenticated($isAuthenticated)
    {
        throw new \RuntimeException('Cannot change authenticated once initialized.');
    }

    public function serialize()
    {
        return serialize([$this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes, $this->activeTwoFactorProviders]);
    }

    public function unserialize($serialized)
    {
        list($this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes, $this->activeTwoFactorProviders) = unserialize($serialized);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function getAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }

        return $this->attributes[$name];
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __toString()
    {
        return $this->getUsername();
    }
}
