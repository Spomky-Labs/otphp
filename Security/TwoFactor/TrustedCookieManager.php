<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;

class TrustedCookieManager
{

    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;

    /**
     * @var string $cookieName
     */
    private $cookieName;

    /**
     * @var integer $cookieLifetime
     */
    private $cookieLifetime;

    /**
     * Construct a manager for the trusted cookie
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, $cookieName, $cookieLifetime)
    {
        $this->em = $em;
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
    }

    /**
     * Check if request has trusted cookie and if it's valid
     *
     * @param \Scheb\TwoFactorBundle\Model\TrustedComputerInterface $user
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function isTrustedComputer(Request $request, TrustedComputerInterface $user)
    {
        if ($request->cookies->has($this->cookieName)) {
            $tokenList = explode(";", $request->cookies->get($this->cookieName));

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
     * Create a cookie for trusted computer
     *
     * @param \Scheb\TwoFactorBundle\Model\TrustedComputerInterface $user
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function createTrustedCookie(Request $request, TrustedComputerInterface $user)
    {
        $tokenList = $request->cookies->get($this->cookieName, null);

        // Generate new token
        $token = TrustedTokenGenerator::generateToken(32);
        $tokenList .= ($tokenList !== null ? ";" : "").$token;
        $validUntil = new \DateTime("+".$this->cookieLifetime." seconds");

        // Add token to user entity
        $user->addTrustedComputer($token, $validUntil);
        $this->em->persist($user);
        $this->em->flush();

        // Create cookie
        return new Cookie($this->cookieName, $tokenList, $validUntil, "/");
    }
}
