<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Lcobucci\JWT\Token;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\JwtTokenEncoder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceToken;
use Scheb\TwoFactorBundle\Tests\TestCase;

class TrustedDeviceTokenTest extends TestCase
{
    /**
     * @var TrustedDeviceToken
     */
    private $trustedToken;

    protected function setUp()
    {
        $jwtToken = $this->createMock(Token::class);
        $jwtToken
            ->expects($this->any())
            ->method('getClaim')
            ->willReturnMap([
                [JwtTokenEncoder::CLAIM_USERNAME, false, 'username'],
                [JwtTokenEncoder::CLAIM_FIREWALL, false, 'firewallName'],
                [JwtTokenEncoder::CLAIM_VERSION, false, 1],
            ]);
        $jwtToken
            ->expects($this->any())
            ->method('__toString')
            ->willReturn('serializedToken');

        $this->trustedToken = new TrustedDeviceToken($jwtToken);
    }

    /**
     * @test
     */
    public function authenticatesRealm_usernameAndFirewallNameMatches_returnTrue()
    {
        $returnValue = $this->trustedToken->authenticatesRealm('username', 'firewallName');
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     * @dataProvider provideWrongUsernameFirewallNameCombination
     */
    public function authenticatesRealm_usernameAndFirewallNameDiffernt_returnFalse(string $username, string $firewallName)
    {
        $returnValue = $this->trustedToken->authenticatesRealm($username, $firewallName);
        $this->assertFalse($returnValue);
    }

    public function provideWrongUsernameFirewallNameCombination(): array
    {
        return [
            ['wrongUsername', 'firewallName'],
            ['username', 'wrongFirewallName'],
        ];
    }

    /**
     * @test
     */
    public function versionMatches_sameVersion_returnTrue()
    {
        $returnValue = $this->trustedToken->versionMatches(1);
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function versionMatches_differentVersion_returnFalse()
    {
        $returnValue = $this->trustedToken->versionMatches(2);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function serialize_encodeToken_returnEncodedString()
    {
        $returnValue = $this->trustedToken->serialize();
        $this->assertEquals('serializedToken', $returnValue);
    }
}
