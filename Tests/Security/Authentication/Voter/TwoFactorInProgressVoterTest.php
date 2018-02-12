<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Authentication\Voter;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\Authentication\Voter\TwoFactorInProgressVoter;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TwoFactorInProgressVoterTest extends TestCase
{
    /**
     * @var TwoFactorInProgressVoter
     */
    private $voter;

    protected function setUp()
    {
        $this->voter = new TwoFactorInProgressVoter();
    }

    /**
     * @test
     */
    public function vote_isNotTwoFactorToken_returnAbstain()
    {
        $token = $this->createMock(TokenInterface::class);
        $returnValue = $this->voter->vote($token, null, [AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY]);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $returnValue);
    }

    /**
     * @test
     * @dataProvider provideAttributeAndExpectedResult
     */
    public function vote_isTwoFactorToken_returnAbstain($checkAttribute, $expectedResult)
    {
        $token = $this->createMock(TwoFactorToken::class);
        $returnValue = $this->voter->vote($token, null, [$checkAttribute]);
        $this->assertEquals($expectedResult, $returnValue);
    }

    public function provideAttributeAndExpectedResult(): array
    {
        return [
            // Abstain
            [null, VoterInterface::ACCESS_ABSTAIN],
            ['any', VoterInterface::ACCESS_ABSTAIN],
            [AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED, VoterInterface::ACCESS_ABSTAIN],
            [AuthenticatedVoter::IS_AUTHENTICATED_FULLY, VoterInterface::ACCESS_ABSTAIN],

            // Granted
            [AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY, VoterInterface::ACCESS_GRANTED],
            [TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS, VoterInterface::ACCESS_GRANTED],
        ];
    }
}
