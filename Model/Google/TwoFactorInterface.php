<?php
namespace Scheb\TwoFactorBundle\Model\Google;

interface TwoFactorInterface
{

    /**
     * Return the user name
     *
     * @return string
     */
    public function getUsername();

    /**
     * Return the Google Authenticator code
     *
     * @return string
     */
    public function getGoogleAuthenticatorSecret();

    /**
     * Set the Google Authenticator code
     *
     * @param integer $GoogleAuthenticatorSecret
     */
    public function setGoogleAuthenticatorSecret($GoogleAuthenticatorSecret);
}