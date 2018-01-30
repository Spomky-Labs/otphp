<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class CodeGenerator implements CodeGeneratorInterface
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var AuthCodeMailerInterface
     */
    private $mailer;

    /**
     * @var int
     */
    private $digits;

    public function __construct(PersisterInterface $persister, AuthCodeMailerInterface $mailer, int $digits)
    {
        $this->persister = $persister;
        $this->mailer = $mailer;
        $this->digits = $digits;
    }

    public function generateAndSend(TwoFactorInterface $user): void
    {
        $min = pow(10, $this->digits - 1);
        $max = pow(10, $this->digits) - 1;
        $code = $this->generateCode($min, $max);
        $user->setEmailAuthCode($code);
        $this->persister->persist($user);
        $this->mailer->sendAuthCode($user);
    }

    protected function generateCode(int $min, int $max): int
    {
        return random_int($min, $max);
    }
}
