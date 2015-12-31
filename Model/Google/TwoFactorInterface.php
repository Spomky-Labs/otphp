<?php

namespace Scheb\TwoFactorBundle\Model\Google;

interface TwoFactorInterface
{
    /**
     * Return the user name.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Return the Google Authenticator secret
     * When an empty string or null is returned, the Google authentication is disabled.
     *
     * @return string|null
     */
    public function getGoogleAuthenticatorSecret();

    /**
     * Set the Google Authenticator secret.
     *
     * @param int $googleAuthenticatorSecret
     */
    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret);
}
