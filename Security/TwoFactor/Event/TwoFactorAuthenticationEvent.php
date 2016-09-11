<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorAuthenticationEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @param Request $request
     * @param TokenInterface $token
     */
    public function __construct(Request $request, TokenInterface $token) {
        $this->request = $request;
        $this->token = $token;
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @return TokenInterface
     */
    public function getToken() {
        return $this->token;
    }
}
