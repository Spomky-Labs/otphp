<?php
namespace Scheb\TwoFactorBundle\Tests;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailer;

class AuthCodeMailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $swiftMailer;

    /**
     * @var \Scheb\TwoFactorBundle\Mailer\AuthCodeMailer
     */
    private $mailer;

    public function setUp()
    {
        $this->swiftMailer = $this->getMockBuilder("Swift_Mailer")
            ->disableOriginalConstructor()
            ->getMock();

        $this->mailer = new AuthCodeMailer($this->swiftMailer, "sender@example.com");
    }

    /**
     * @test
     */
    public function sendAuthCode_withUserObject_sendEmail()
    {
        //Mock the user object
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface");
        $user
            ->expects($this->once())
            ->method("getEmail")
            ->will($this->returnValue("recipient@example.com"));

        $this->mailer->sendAuthCode($user);
    }

}