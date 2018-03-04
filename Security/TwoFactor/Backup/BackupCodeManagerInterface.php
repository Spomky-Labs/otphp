<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Backup;

interface BackupCodeManagerInterface
{
    /**
     * Check if the code is a valid backup code of the user.
     *
     * @param mixed  $user
     * @param string $code
     *
     * @return bool
     */
    public function isBackupCode($user, string $code): bool;

    /**
     * Invalidate a backup code from a user.
     *
     * @param mixed  $user
     * @param string $code
     */
    public function invalidateBackupCode($user, string $code): void;
}
