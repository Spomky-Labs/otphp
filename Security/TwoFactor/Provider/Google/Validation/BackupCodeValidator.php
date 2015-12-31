<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeValidator as BasicBackupCodeValidator;

class BackupCodeValidator implements CodeValidatorInterface
{
    /**
     * @var string
     */
    private $backupCodeValidator;

    /**
     * @var string
     */
    private $validator;

    /**
     * Initialize with the name of the auth code parameter.
     *
     * @param BasicBackupCodeValidator $backupCodeValidator
     * @param CodeValidatorInterface   $validator
     */
    public function __construct(BasicBackupCodeValidator $backupCodeValidator, CodeValidatorInterface $validator)
    {
        $this->backupCodeValidator = $backupCodeValidator;
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
        if ($user instanceof BackupCodeInterface && $this->backupCodeValidator->checkCode($user, $code)) {
            return true;
        }

        return $this->validator->checkCode($user, $code);
    }
}
