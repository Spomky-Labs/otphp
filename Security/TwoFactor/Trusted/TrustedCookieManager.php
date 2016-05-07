<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class TrustedCookieManager
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var TrustedTokenGenerator
     */
    private $tokenGenerator;

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
     * Construct a manager for the trusted cookie.
     *
     * @param PersisterInterface    $persister
     * @param TrustedTokenGenerator $tokenGenerator
     * @param string                $cookieName
     * @param int                   $cookieLifetime
     */
    public function __construct(PersisterInterface $persister, TrustedTokenGenerator $tokenGenerator, $cookieName, $cookieLifetime, $cookieSecure)
    {
        $this->persister = $persister;
        $this->tokenGenerator = $tokenGenerator;
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
        $this->cookieSecure = $cookieSecure;
    }

    /**
     * Check if request has trusted cookie and if it's valid.
     *
     * @param TrustedComputerInterface $user
     * @param Request                  $request
     *
     * @return bool
     */
    public function isTrustedComputer(Request $request, TrustedComputerInterface $user)
    {
        if ($request->cookies->has($this->cookieName)) {
            $tokenList = explode(';', $request->cookies->get($this->cookieName));

            // Interate over trusted tokens and validate them
            foreach ($tokenList as $token) {
                if ($user->isTrustedComputer($token)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create a cookie for trusted computer.
     *
     * @param TrustedComputerInterface $user
     * @param Request                  $request
     *
     * @return Cookie
     */
    public function createTrustedCookie(Request $request, TrustedComputerInterface $user)
    {
        $tokenList = $request->cookies->get($this->cookieName, null);

        // Generate new token
        $token = $this->tokenGenerator->generateToken(32);
        $tokenList .= ($tokenList !== null ? ';' : '').$token;
        $validUntil = $this->getDateTimeNow()->add(new \DateInterval('PT'.$this->cookieLifetime.'S'));

        // Add token to user entity
        $user->addTrustedComputer($token, $validUntil);
        $this->persister->persist($user);

        // Create cookie
        return new Cookie($this->cookieName, $tokenList, $validUntil, '/', null, $this->cookieSecure);
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
