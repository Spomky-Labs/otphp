<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Backup;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class BackupCodeManager implements BackupCodeManagerInterface
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    public function __construct(PersisterInterface $persister)
    {
        $this->persister = $persister;
    }

    public function isBackupCode($user, string $code): bool
    {
        if ($user instanceof BackupCodeInterface) {
            return $user->isBackupCode($code);
        }

        return false;
    }

    public function invalidateBackupCode($user, string $code): void
    {
        if ($user instanceof BackupCodeInterface) {
            $user->invalidateBackupCode($code);
            $this->persister->persist($user);
        }
    }
}
