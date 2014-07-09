<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class TrustedCookieManager
{

    /**
     * @var \Doctrine\ORM\EntityManager $persister
     */
    private $persister;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator $tokenGenerator
     */
    private $tokenGenerator;

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
     * @param \Scheb\TwoFactorBundle\Model\PersisterInterface                         $persister
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator $tokenGenerator
     * @param string                                                                  $cookieName
     * @param integer                                                                 $cookieLifetime
     */
    public function __construct(PersisterInterface $persister, TrustedTokenGenerator $tokenGenerator, $cookieName, $cookieLifetime)
    {
        $this->persister = $persister;
        $this->tokenGenerator = $tokenGenerator;
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
    }

    /**
     * Check if request has trusted cookie and if it's valid
     *
     * @param \Scheb\TwoFactorBundle\Model\TrustedComputerInterface $user
     * @param \Symfony\Component\HttpFoundation\Request             $request
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
     * @param \Symfony\Component\HttpFoundation\Request             $request
     */
    public function createTrustedCookie(Request $request, TrustedComputerInterface $user)
    {
        $tokenList = $request->cookies->get($this->cookieName, null);

        // Generate new token
        $token = $this->tokenGenerator->generateToken(32);
        $tokenList .= ($tokenList !== null ? ";" : "").$token;
        $validUntil = $this->getDateTimeNow()->add(new \DateInterval("PT".$this->cookieLifetime."S"));

        // Add token to user entity
        $user->addTrustedComputer($token, $validUntil);
        $this->persister->persist($user);

        // Create cookie
        return new Cookie($this->cookieName, $tokenList, $validUntil, "/");
    }

    /**
     * Return current DateTime object
     *
     * @return \DateTime
     * @codeCoverageIgnore
     */
    protected function getDateTimeNow()
    {
        return new \DateTime();
    }

}
