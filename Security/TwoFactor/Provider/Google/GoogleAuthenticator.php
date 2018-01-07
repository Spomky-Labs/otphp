<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Google\Authenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class GoogleAuthenticator implements GoogleAuthenticatorInterface
{
    /**
     * @var string
     */
    private $server;

    /**
     * @var BaseGoogleAuthenticator
     */
    private $authenticator;

    /**
     * @var string
     */
    private $issuer;

    /**
     * Construct the helper service for Google Authenticator.
     *
     * @param BaseGoogleAuthenticator $authenticator
     * @param string                  $server
     * @param string                  $issuer
     */
    public function __construct(BaseGoogleAuthenticator $authenticator, $server, $issuer)
    {
        $this->authenticator = $authenticator;
        $this->server = $server;
        $this->issuer = $issuer;
    }

    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param string             $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $this->authenticator->checkCode($user->getGoogleAuthenticatorSecret(), $code);
    }

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app.
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getUrl(TwoFactorInterface $user)
    {
        $encoder = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=';

        return $encoder.urlencode($this->getQRContent($user));
    }

    /**
     * Generate the content for a QR-Code to be scanned by Google Authenticator
     * Use this method if you don't want to use google charts to display the qr-code
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getQRContent(TwoFactorInterface $user)
    {
        $userAndHost = rawurlencode($user->getUsername()).($this->server ? '@'.rawurlencode($this->server) : '');
        if ($this->issuer) {
            $qrContent = sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode($this->issuer),
                $userAndHost,
                $user->getGoogleAuthenticatorSecret(),
                rawurlencode($this->issuer)
            );
        } else {
            $qrContent = sprintf(
                'otpauth://totp/%s?secret=%s',
                $userAndHost,
                $user->getGoogleAuthenticatorSecret()
            );
        }

        return $qrContent;
    }

    /**
     * Generate a new secret for Google Authenticator.
     *
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecret();
    }
}
