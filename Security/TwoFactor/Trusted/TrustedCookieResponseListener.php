<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class TrustedCookieResponseListener
{
    /**
     * @var TrustedDeviceTokenStorage
     */
    private $trustedTokenStorage;

    /**
     * @var int
     */
    private $trustedTokenLifetime;

    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var bool
     */
    private $cookieSecure;

    /**
     * @var string
     */
    private $cookieSameSite;

    public function __construct(
        TrustedDeviceTokenStorage $trustedTokenStorage,
        int $trustedTokenLifetime,
        string $cookieName,
        bool $cookieSecure,
        string $cookieSameSite
    ) {
        $this->trustedTokenStorage = $trustedTokenStorage;
        $this->trustedTokenLifetime = $trustedTokenLifetime;
        $this->cookieName = $cookieName;
        $this->cookieSecure = $cookieSecure;
        $this->cookieSameSite = $cookieSameSite;
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if ($this->trustedTokenStorage->hasUpdatedCookie()) {
            $domain = null;
            $requestHost = $event->getRequest()->getHost();
            if ('localhost' !== $requestHost) {
                $domain = '.'.$requestHost;
            }

            // Set the cookie
            $cookie = new Cookie(
                $this->cookieName,
                $this->trustedTokenStorage->getCookieValue(),
                $this->getValidUntil(),
                '/',
                $domain,
                $this->cookieSecure,
                true,
                false,
                $this->cookieSameSite
            );

            $response = $event->getResponse();
            $response->headers->setCookie($cookie);
        }
    }

    private function getValidUntil(): \DateTime
    {
        return $this->getDateTimeNow()->add(new \DateInterval('PT'.$this->trustedTokenLifetime.'S'));
    }

    protected function getDateTimeNow(): \DateTime
    {
        return new \DateTime();
    }
}
