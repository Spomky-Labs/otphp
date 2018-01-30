<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class TrustedCookieManager
{
    /**
     * @var TrustedTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var TrustedComputerManagerInterface
     */
    private $trustedComputerManager;

    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var int
     */
    private $cookieLifetime;

    /**
     * @var bool
     */
    private $cookieSecure;

    /**
     * @var string
     */
    private $cookieSameSite;

    public function __construct(
        TrustedTokenGenerator $tokenGenerator,
        TrustedComputerManagerInterface $trustedComputerManager,
        string $cookieName,
        int $cookieLifetime,
        bool $cookieSecure,
        string $cookieSameSite
    )
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->trustedComputerManager = $trustedComputerManager;
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
        $this->cookieSecure = $cookieSecure;
        $this->cookieSameSite = $cookieSameSite;
    }

    public function isTrustedComputer(Request $request, $user): bool
    {
        if ($request->cookies->has($this->cookieName)) {
            $tokenList = explode(';', $request->cookies->get($this->cookieName));

            // Iterate over trusted tokens and validate them
            foreach ($tokenList as $token) {
                if ($this->trustedComputerManager->isTrustedComputer($user, $token)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function createTrustedCookie(Request $request, $user): Cookie
    {
        $tokenList = $request->cookies->get($this->cookieName, null);

        // Generate new token
        $token = $this->tokenGenerator->generateToken(32);
        $tokenList .= ($tokenList !== null ? ';' : '').$token;
        $validUntil = $this->getDateTimeNow()->add(new \DateInterval('PT'.$this->cookieLifetime.'S'));

        // Add token to user entity
        $this->trustedComputerManager->addTrustedComputer($user, $token, $validUntil);

        $domain = null;
        $requestHost = $request->getHost();
        if ($requestHost !== 'localhost') {
            $domain = '.' . $requestHost;
        }

        // Create cookie
        return new Cookie($this->cookieName, $tokenList, $validUntil, '/', $domain, $this->cookieSecure, true, false, $this->cookieSameSite);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getDateTimeNow(): \DateTime
    {
        return new \DateTime();
    }
}
