<?php

namespace Scheb\TwoFactorBundle\Tests\Mailer;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailer;

class AuthCodeMailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $swiftMailer;

    /**
     * @var AuthCodeMailer
     */
    private $mailer;

    public function setUp()
    {
        $this->swiftMailer = $this->getMockBuilder('Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mailer = new AuthCodeMailer($this->swiftMailer, 'sender@example.com', 'Sender Name');
    }

    /**
     * @test
     */
    public function sendAuthCode_withUserObject_sendEmail()
    {
        //Stub the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');
        $user
            ->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('recipient@example.com'));
        $user
            ->expects($this->any())
            ->method('getEmailAuthCode')
            ->will($this->returnValue(1234));

        $messageValidator = function ($subject) {
            /* @var \Swift_Message $subject */
            return key($subject->getTo()) === 'recipient@example.com'
                && $subject->getFrom() === array('sender@example.com' => 'Sender Name')
                && $subject->getSubject() === 'Authentication Code'
                && $subject->getBody() === 1234;
        };

        //Expect mail to be sent
        $this->swiftMailer
            ->expects($this->once())
            ->method('send')
            ->with($this->logicalAnd($this->isInstanceof('Swift_Message'), $this->callback($messageValidator)));

        $this->mailer->sendAuthCode($user);
    }
}
