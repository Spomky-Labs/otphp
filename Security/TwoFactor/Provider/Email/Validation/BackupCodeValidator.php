<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
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

    /**
     * Initialize with the name of the auth code parameter.
     *
     * @param BackupCodeComparator $backupCodeComparator
     * @param CodeValidatorInterface   $validator
     */
    public function __construct(BackupCodeComparator $backupCodeComparator, CodeValidatorInterface $validator)
    {
        $this->backupCodeComparator = $backupCodeComparator;
        $this->validator = $validator;
    }

    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param int                $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        if ($user instanceof BackupCodeInterface && $this->backupCodeComparator->checkCode($user, $code)) {
            return true;
        }

        return $this->validator->checkCode($user, $code);
    }
}
