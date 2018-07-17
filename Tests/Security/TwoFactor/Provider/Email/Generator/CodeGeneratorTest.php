<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Generator;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGenerator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class CodeGeneratorTest extends TestCase
{
    /**
     * @var MockObject|PersisterInterface
     */
    private $persister;

    /**
     * @var MockObject|AuthCodeMailerInterface
     */
    private $mailer;

    /**
     * @var TestableCodeGenerator
     */
    private $authCodeManager;

    protected function setUp()
    {
        $this->persister = $this->createMock(PersisterInterface::class);

        $this->mailer = $this->createMock(AuthCodeMailerInterface::class);

        $this->authCodeManager = new TestableCodeGenerator($this->persister, $this->mailer, 5);
        $this->authCodeManager->testCode = 12345;
    }

    /**
     * @test
     */
    public function generateAndSend_useOriginalCodeGenerator_codeBetweenRange()
    {
        //Mock the user object
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->once())
            ->method('setEmailAuthCode')
            ->with($this->logicalAnd(
                $this->greaterThanOrEqual(10000),
                $this->lessThanOrEqual(99999)
            ));

        //Construct test subject with original class
        $authCodeManager = new CodeGenerator($this->persister, $this->mailer, 5);
        $authCodeManager->generateAndSend($user);
    }

    /**
     * @test
     */
    public function generateAndSend_checkCodeRange_validMinAndMax()
    {
        //Stub the user object
        $user = $this->createMock(TwoFactorInterface::class);

        $this->authCodeManager->generateAndSend($user);

        //Validate min and max value
        $this->assertEquals(10000, $this->authCodeManager->lastMin);
        $this->assertEquals(99999, $this->authCodeManager->lastMax);
    }

    /**
     * @test
     */
    public function generateAndSend_generateNewCode_persistsCode()
    {
        //Mock the user object
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->once())
            ->method('setEmailAuthCode')
            ->with(12345);

        //Mock the persister
        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->authCodeManager->generateAndSend($user);
    }

    /**
     * @test
     */
    public function generateAndSend_generateNewCode_sendMail()
    {
        //Stub the user object
        $user = $this->createMock(TwoFactorInterface::class);

        //Mock the mailer
        $this->mailer
            ->expects($this->once())
            ->method('sendAuthCode')
            ->with($user);

        $this->authCodeManager->generateAndSend($user);
    }
}

//Make the AuthCodeManager class testable
class TestableCodeGenerator extends CodeGenerator
{
    public $testCode;
    public $lastMin;
    public $lastMax;

    protected function generateCode(int $min, int $max): int
    {
        $this->lastMin = $min;
        $this->lastMax = $max;

        return $this->testCode;
    }
}
