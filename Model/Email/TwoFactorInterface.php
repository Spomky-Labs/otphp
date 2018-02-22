<?php

namespace Scheb\TwoFactorBundle\Model\Email;

interface TwoFactorInterface
{
    /**
     * Return true if the user should do two-factor authentication.
     *
     * @return bool
     */
    public function isEmailAuthEnabled(): bool;

    /**
     * Return user email address.
     *
     * @return string
     */
    public function getEmailAuthRecipient(): string;

    /**
     * Return the authentication code.
     *
     * @return string
     */
    public function getEmailAuthCode(): string;

    /**
     * Set the authentication code.
     *
     * @param string $authCode
     */
    public function setEmailAuthCode(string $authCode): void;
}
