<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Backup;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class BackupCodeValidator
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * Construct a validator for backup codes.
     *
     * @param PersisterInterface $persister
     */
    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    /**
     * Check if code is a valid backup code.
     *
     * @param BackupCodeInterface $user
     * @param string              $code
     *
     * @return bool
     */
    public function checkCode(BackupCodeInterface $user, $code)
    {
        if ($user->isBackupCode($code)) {
            $user->invalidateBackupCode($code);
            $this->persister->persist($user);

            return true;
        }

        return false;
    }
}
