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
     * Return users email address.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Return the authentication code.
     *
     * @return int
     */
    public function getEmailAuthCode(): int;

    /**
     * Set the authentication code.
     *
     * @param int $authCode
     */
    public function setEmailAuthCode(int $authCode): void;
}
