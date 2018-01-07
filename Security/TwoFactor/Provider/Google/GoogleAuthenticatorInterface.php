<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Google\Authenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface GoogleAuthenticatorInterface
{

    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param string             $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code);

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app.
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getUrl(TwoFactorInterface $user);

    /**
     * Generate the content for a QR-Code to be scanned by Google Authenticator
     * Use this method if you don't want to use google charts to display the qr-code
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getQRContent(TwoFactorInterface $user);

    /**
     * Generate a new secret for Google Authenticator.
     *
     * @return string
     */
    public function generateSecret();
}
