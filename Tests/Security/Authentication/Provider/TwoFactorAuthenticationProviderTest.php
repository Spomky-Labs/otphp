<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Authentication\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\InvalidTwoFactorCodeException;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\TwoFactorProviderNotFoundException;
use Scheb\TwoFactorBundle\Security\Authentication\Provider\TwoFactorAuthenticationProvider;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorAuthenticationProviderTest extends TestCase
{
    /**
     * @var MockObject|TwoFactorProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var MockObject|BackupCodeManagerInterface
     */
    private $backupCodeManager;

    /**
     * @var MockObject|UserInterface
     */
    private $user;

    /**
     * @var MockObject|TokenInterface
     */
    private $authenticatedToken;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $twoFactorProvider1;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $twoFactorProvider2;

    /**
     * @var TwoFactorToken
     */
    private $twoFactorToken;

    /**
     * @var TwoFactorAuthenticationProvider
     */
    private $authenticationProvider;

    protected function setUp()
    {
        $this->providerRegistry = $this->createMock(TwoFactorProviderRegistry::class);
        $this->backupCodeManager = $this->createMock(BackupCodeManagerInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->authenticatedToken = $this->createMock(TokenInterface::class);
        $this->authenticatedToken
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user);

        $this->twoFactorProvider1 = $this->createMock(TwoFactorProviderInterface::class);
        $this->twoFactorProvider2 = $this->createMock(TwoFactorProviderInterface::class);
    }

    private function createAuthenticationProviderWithMultiFactor(bool $multiFactor)
    {
        $this->providerRegistry
            ->expects($this->any())
            ->method('getProvider')
            ->willReturnCallback(function (string $providerName) {
                switch ($providerName) {
                    case 'provider1':
                        return $this->twoFactorProvider1;
                    case 'provider2':
                        return $this->twoFactorProvider2;
                    default:
                        throw new \InvalidArgumentException();
                }
            });

        $options['multi_factor'] = $multiFactor;
        $this->authenticationProvider = new TwoFactorAuthenticationProvider('firewallName', $options, $this->providerRegistry, $this->backupCodeManager);
    }

    public function createTwoFactorToken(string $firewallName, ?string $credentials, array $twoFactorProviders = []): TwoFactorToken
    {
        $this->twoFactorToken = new TwoFactorToken($this->authenticatedToken, $credentials, $firewallName, $twoFactorProviders);

        return $this->twoFactorToken;
    }

    public function createSupportedTwoFactorTokenWithProviders(array $twoFactorProviders)
    {
        return $this->createTwoFactorToken('firewallName', 'credentials', $twoFactorProviders);
    }

    private function stubTwoFactorProviderCredentialsAreValid(MockObject $provider, bool $isValid): void
    {
        $provider
            ->expects($this->any())
            ->method('validateAuthenticationCode')
            ->willReturn($isValid);
    }

    /**
     * @test
     */
    public function authenticate_noTwoFactorToken_returnNull()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createMock(TokenInterface::class);

        $returnValue = $this->authenticationProvider->authenticate($token);
        $this->assertNull($returnValue);
    }

    /**
     * @test
     */
    public function authenticate_differentFirewallName_returnNull()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createTwoFactorToken('otherFirewallName', 'credentials');

        $returnValue = $this->authenticationProvider->authenticate($token);
        $this->assertNull($returnValue);
    }

    /**
     * @test
     */
    public function authenticate_noCredentials_returnSameToken()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createTwoFactorToken('firewallName', null);

        $returnValue = $this->authenticationProvider->authenticate($token);
        $this->assertSame($token, $returnValue);
    }

    /**
     * @test
     */
    public function authenticate_twoFactorProviderMissing_throwTwoFactorProviderNotFoundException()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['unknownProvider']);

        $this->expectException(TwoFactorProviderNotFoundException::class);

        $this->authenticationProvider->authenticate($token);
    }

    /**
     * @test
     */
    public function authenticate_twoFactorProviderExists_checkCode()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1']);

        $this->twoFactorProvider1
            ->expects($this->once())
            ->method('validateAuthenticationCode')
            ->willReturn(true);

        $this->authenticationProvider->authenticate($token);
    }

    /**
     * @test
     */
    public function authenticate_backupCodeValid_invalidateBackupCode()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1']);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider1, false);

        $this->backupCodeManager
            ->expects($this->once())
            ->method('isBackupCode')
            ->with($this->user, 'credentials')
            ->willReturn(true);

        $this->backupCodeManager
            ->expects($this->once())
            ->method('invalidateBackupCode')
            ->with($this->user, 'credentials');

        $this->authenticationProvider->authenticate($token);
    }

    /**
     * @test
     */
    public function authenticate_backupCodeInvalid_throwInvalidTwoFactorCodeException()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1']);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider1, false);

        $this->backupCodeManager
            ->expects($this->once())
            ->method('isBackupCode')
            ->willReturn(false);

        $this->expectException(InvalidTwoFactorCodeException::class);

        $this->authenticationProvider->authenticate($token);
    }

    /**
     * @test
     */
    public function authenticate_noMultiFactorAuthentication_returnAuthenticatedToken()
    {
        $this->createAuthenticationProviderWithMultiFactor(false);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1', 'provider2']);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider1, true);

        $returnValue = $this->authenticationProvider->authenticate($token);
        $this->assertSame($this->authenticatedToken, $returnValue);
    }

    /**
     * @test
     */
    public function authenticate_multiFactorAuthenticationNotComplete_returnTwoFactorToken()
    {
        $this->createAuthenticationProviderWithMultiFactor(true);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1', 'provider2']);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider1, true);

        $returnValue = $this->authenticationProvider->authenticate($token);
        $this->assertSame($token, $returnValue);
        $this->assertEquals('provider2', $token->getCurrentTwoFactorProvider());
        $this->assertFalse($token->allTwoFactorProvidersAuthenticated());
    }

    /**
     * @test
     */
    public function authenticate_multiFactorAuthenticationIsComplete_returnAuthenticatedToken()
    {
        $this->createAuthenticationProviderWithMultiFactor(true);
        $token = $this->createSupportedTwoFactorTokenWithProviders(['provider1', 'provider2']);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider1, true);
        $this->stubTwoFactorProviderCredentialsAreValid($this->twoFactorProvider2, true);

        $this->authenticationProvider->authenticate($token); // Two-factor provider 1 successful
        $returnValue = $this->authenticationProvider->authenticate($token); // Two-factor provider 2 successful

        $this->assertSame($this->authenticatedToken, $returnValue);
    }
}
