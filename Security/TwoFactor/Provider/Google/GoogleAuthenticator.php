<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Google\Authenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class GoogleAuthenticator
{

    /**
     *
     * @var string $server
     */
    private $server;

    /**
     *
     * @var \Google\Authenticator\GoogleAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * Construct the helper service for Google Authenticator
     *
     * @param \Google\Authenticator\GoogleAuthenticator $authenticator            
     * @param string $server            
     */
    public function __construct(BaseGoogleAuthenticator $authenticator, $server)
    {
        $this->authenticator = $authenticator;
        $this->server = $server;
    }

    /**
     * Validates the code, which was entered by the user
     *
     * @param \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user            
     * @param string $code            
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $this->authenticator->checkCode($user->getGoogleAuthenticatorSecret(), $code);
    }

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app
     *
     * @param \Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface $user            
     * @return string
     */
    public function getUrl(TwoFactorInterface $user)
    {
        return $this->authenticator->getUrl($user->getUsername(), $this->server, $user->getGoogleAuthenticatorSecret());
    }

    /**
     * Generate a new secret for Google Authenticator
     *
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecret();
    }
}