<?php

namespace Scheb\TwoFactorBundle\Model;

interface BackupCodeInterface
{
    /**
     * Check if it is a valid backup code.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isBackupCode(string $code): bool;

    /**
     * Invalidate a backup code.
     *
     * @param string $code
     */
    public function invalidateBackupCode(string $code): void;
}
