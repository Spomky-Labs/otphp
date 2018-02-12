<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\JwtTokenEncoder;
use Scheb\TwoFactorBundle\Tests\TestCase;

class JwtTokenEncoderTest extends TestCase
{
    private const CLAIM = 'test';
    private const TOKEN_ID = 'tokenId';
    private const APPLICATION_SECRET = 'applicationSecret';

    /**
     * @var Sha256
     */
    private $signer;

    /**
     * @var JwtTokenEncoder
     */
    private $encoder;

    protected function setUp()
    {
        $this->signer = new Sha256();
        $this->encoder = new JwtTokenEncoder(self::APPLICATION_SECRET);
    }

    private function createToken(int $expirationDate): string
    {
        return (string) (new Builder())
            ->set(self::CLAIM, self::TOKEN_ID)
            ->setExpiration($expirationDate)
            ->sign($this->signer, self::APPLICATION_SECRET)
            ->getToken();
    }

    /**
     * @test
     */
    public function generateToken_withClaims_returnEncodedToken()
    {
        $jwtToken = $this->encoder->generateToken('username', 'firewallName', 1, new \DateTime());
        $this->assertInstanceOf(Token::class, $jwtToken);
        $this->assertEquals('username', $jwtToken->getClaim(JwtTokenEncoder::CLAIM_USERNAME, false));
        $this->assertEquals('firewallName', $jwtToken->getClaim(JwtTokenEncoder::CLAIM_FIREWALL, false));
        $this->assertEquals(1, $jwtToken->getClaim(JwtTokenEncoder::CLAIM_VERSION, false));
        $this->assertFalse($jwtToken->isExpired(new \DateTime('-100 seconds')));
        $this->assertTrue($jwtToken->isExpired(new \DateTime('+100 seconds')));
    }

    /**
     * @test
     */
    public function decodeToken_invalidToken_returnNull()
    {
        $decodedToken = $this->encoder->decodeToken('invalidToken');
        $this->assertNull($decodedToken);
    }

    /**
     * @test
     */
    public function decodeToken_expiredToken_returnNull()
    {
        $encodedToken = $this->createToken(time() - 1000);
        $decodedToken = $this->encoder->decodeToken($encodedToken);
        $this->assertNull($decodedToken);
    }

    /**
     * @test
     */
    public function decodeToken_validToken_returnDecodedToken()
    {
        $encodedToken = $this->createToken(time() + 1000);
        $decodedToken = $this->encoder->decodeToken($encodedToken);
        $this->assertInstanceOf(Token::class, $decodedToken);
        $this->assertEquals(self::TOKEN_ID, $decodedToken->getClaim(self::CLAIM, false));
    }
}
