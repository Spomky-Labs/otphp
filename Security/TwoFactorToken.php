<?php
namespace Scheb\TwoFactorBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorToken implements TokenInterface {

    private $authenticatedToken;
    private $credentials;

    public function __construct(TokenInterface $authenticatedToken, $credentials)
    {
        $this->credentials = $credentials;
        $this->authenticatedToken = $authenticatedToken;
    }

    public function getUser() {
        return $this->authenticatedToken->getUser();
    }

    public function getUsername() {
        return '2fa:' . get_class($this->authenticatedToken) . ':' . $this->authenticatedToken->getUsername();
    }

    public function getAuthenticatedToken() {
        return $this->authenticatedToken;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function eraseCredentials()
    {
        $this->credentials = null;
    }

    public function serialize()
    {
        return serialize(array($this->authenticatedToken, $this->credentials));
    }

    public function unserialize($serialized)
    {
        list($this->authenticatedToken, $this->credentials) = unserialize($serialized);
    }

    public function __toString() {
        return $this->getUsername();
    }

    public function getRoles() {
        return [];
    }

    public function setUser($user) {
        $this->authenticatedToken->setUser($user);
    }

    public function isAuthenticated() {
        return false;
    }

    public function setAuthenticated($isAuthenticated) {
        throw new \RuntimeException('Not allowed.');
    }

    public function getAttributes() {
        throw new \RuntimeException('Not allowed.');
    }

    public function setAttributes(array $attributes) {
        throw new \RuntimeException('Not allowed.');
    }

    public function hasAttribute($name) {
        throw new \RuntimeException('Not allowed.');
    }

    public function getAttribute($name) {
        throw new \RuntimeException('Not allowed.');
    }

    public function setAttribute($name, $value) {
        throw new \RuntimeException('Not allowed.');
    }
}
