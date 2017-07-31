<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

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

    /**
     * Construct a manager for the trusted cookie.
     *
     * @param TrustedTokenGenerator           $tokenGenerator
     * @param TrustedComputerManagerInterface $trustedComputerManager
     * @param string                          $cookieName
     * @param int                             $cookieLifetime
     * @param boolean                         $cookieSecure
     * @param string                          $cookieSameSite
     */
    public function __construct(
        TrustedTokenGenerator $tokenGenerator,
        TrustedComputerManagerInterface $trustedComputerManager,
        $cookieName,
        $cookieLifetime,
        $cookieSecure,
        $cookieSameSite)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->trustedComputerManager = $trustedComputerManager;
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
        $this->cookieSecure = $cookieSecure;
        $this->cookieSameSite = $cookieSameSite;
    }

    /**
     * Check if request has trusted cookie and if it's valid.
     *
     * @param Request $request
     * @param mixed   $user
     *
     *
     * @return bool
     */
    public function isTrustedComputer(Request $request, $user)
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

    /**
     * Create a cookie for trusted computer.
     *
     * @param Request $request
     * @param mixed   $user
     *
     *
     * @return Cookie
     */
    public function createTrustedCookie(Request $request, $user)
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
     * Return current DateTime object.
     *
     * @return \DateTime
     * @codeCoverageIgnore
     */
    protected function getDateTimeNow()
    {
        return new \DateTime();
    }
}
