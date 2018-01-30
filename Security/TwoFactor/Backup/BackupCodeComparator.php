<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Backup;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class BackupCodeComparator
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    public function checkCode(BackupCodeInterface $user, string $code): bool
    {
        if ($user->isBackupCode($code)) {
            $user->invalidateBackupCode($code);
            $this->persister->persist($user);

            return true;
        }

        return false;
    }
}
