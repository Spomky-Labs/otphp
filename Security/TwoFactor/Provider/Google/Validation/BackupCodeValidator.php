<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeComparator;

class BackupCodeValidator implements CodeValidatorInterface
{
    /**
     * @var BackupCodeComparator
     */
    private $backupCodeComparator;

    /**
     * @var string
     */
    private $validator;

    public function __construct(BackupCodeComparator $backupCodeComparator, CodeValidatorInterface $validator)
    {
        $this->backupCodeComparator = $backupCodeComparator;
        $this->validator = $validator;
    }

    public function checkCode(TwoFactorInterface $user, string $code): bool
    {
        if ($user instanceof BackupCodeInterface && $this->backupCodeComparator->checkCode($user, $code)) {
            return true;
        }

        return $this->validator->checkCode($user, $code);
    }
}
