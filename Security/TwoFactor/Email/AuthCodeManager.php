<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Email;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class AuthCodeManager
{

    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;

    /**
     * @var \Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface $mailer
     */
    private $mailer;

    /**
     * Construct the code generator service
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface $mailer
     */
    public function __construct(EntityManager $em, AuthCodeMailerInterface $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    /**
     * Generate a new authentication code an send it to the user
     *
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $user
     */
    public function generateAndSend(TwoFactorInterface $user)
    {
        $code = mt_rand(1000, 9999);
        $user->setEmailAuthCode($code);
        $this->em->persist($user);
        $this->em->flush();
        $this->mailer->sendAuthCode($user);
    }

    /**
     * Validates the code, which was entered by the user
     *
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $user
     * @param $code
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code)
    {
        return $user->getEmailAuthCode() == $code;
    }
}
