<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

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
    public function checkCode(TwoFactorInterface $user, string $code): bool;

    /**
     * Generate the URL of a QR code, which can be scanned by Google Authenticator app.
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getUrl(TwoFactorInterface $user): string;

    /**
     * Generate the content for a QR-Code to be scanned by Google Authenticator
     * Use this method if you don't want to use google charts to display the qr-code.
     *
     * @param TwoFactorInterface $user
     *
     * @return string
     */
    public function getQRContent(TwoFactorInterface $user): string;

    /**
     * Generate a new secret for Google Authenticator.
     *
     * @return string
     */
    public function generateSecret(): string;
}
