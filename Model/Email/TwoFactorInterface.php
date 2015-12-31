<?php

namespace Scheb\TwoFactorBundle\Model\Email;

interface TwoFactorInterface
{
    /**
     * Return true if the user should do two-factor authentication.
     *
     * @return bool
     */
    public function isEmailAuthEnabled();

    /**
     * Return users email address.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Return the authentication code.
     *
     * @return int
     */
    public function getEmailAuthCode();

    /**
     * Set the authentication code.
     *
     * @param int $authCode
     */
    public function setEmailAuthCode($authCode);
}
