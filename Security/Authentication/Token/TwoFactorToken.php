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
    private $attributes = array();

    /**
     * @param TokenInterface $authenticatedToken
     * @param string|null $credentials
     * @param string $providerKey
     */
    public function __construct(TokenInterface $authenticatedToken, $credentials, $providerKey)
    {
        $this->authenticatedToken = $authenticatedToken;
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() {
        return $this->authenticatedToken->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function setUser($user) {
        $this->authenticatedToken->setUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername() {
        return '2fa:' . get_class($this->authenticatedToken) . ':' . $this->authenticatedToken->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    /**
     * @return TokenInterface
     */
    public function getAuthenticatedToken() {
        return $this->authenticatedToken;
    }

    /**
     * @return string
     */
    public function getProviderKey() {
        return $this->providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($isAuthenticated) {
        throw new \RuntimeException('Cannot change authenticated once initialized.');
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->authenticatedToken, $this->credentials, $this->providerKey, $this->attributes) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }

        return $this->attributes[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() {
        return $this->getUsername();
    }
}
