<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Backup;

class NullBackupCodeManager implements BackupCodeManagerInterface
{
    public function isBackupCode($user, string $code): bool
    {
        return false;
    }

    public function invalidateBackupCode($user, string $code): void
    {
    }
}
