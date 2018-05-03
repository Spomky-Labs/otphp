<?php

namespace Scheb\TwoFactorBundle\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Controller\FormController;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\TwoFactorProviderNotFoundException;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallConfig;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class FormControllerTest extends TestCase
{
    private const CURRENT_TWO_FACTOR_PROVIDER = 'provider1';
    private const AUTH_CODE_PARAM_NAME = 'auth_code_param_name';
    private const TRUSTED_PARAM_NAME = 'trusted_param_name';
    private const FIREWALL_NAME = 'firewallName';

    /**
     * @var MockObject|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var MockObject|TwoFactorProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var MockObject|SessionInterface
     */
    private $session;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|TwoFactorFormRendererInterface
     */
    private $formRenderer;

    /**
     * @var MockObject|TwoFactorToken
     */
    private $twoFactorToken;

    /**
     * @var MockObject|TwoFactorFirewallConfig
     */
    private $firewallConfig;

    /**
     * @var FormController
     */
    private $controller;

    /**
     * @var MockObject|TwoFactorFirewallContext
     */
    private $twoFactorFirewallContext;

    protected function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);

        $this->formRenderer = $this->createMock(TwoFactorFormRendererInterface::class);
        $twoFactorProvider = $this->createMock(TwoFactorProviderInterface::class);
        $twoFactorProvider
            ->expects($this->any())
            ->method('getFormRenderer')
            ->willReturn($this->formRenderer);

        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->providerRegistry = $this->createMock(TwoFactorProviderRegistry::class);
        $this->providerRegistry
            ->expects($this->any())
            ->method('getProvider')
            ->with(self::CURRENT_TWO_FACTOR_PROVIDER)
            ->willReturn($twoFactorProvider);

        $this->firewallConfig = $this->createMock(TwoFactorFirewallConfig::class);
        $this->firewallConfig
            ->expects($this->any())
            ->method('getAuthCodeParameterName')
            ->willReturn(self::AUTH_CODE_PARAM_NAME);
        $this->firewallConfig
            ->expects($this->any())
            ->method('getTrustedParameterName')
            ->willReturn(self::TRUSTED_PARAM_NAME);

        $this->twoFactorFirewallContext = $this->createMock(TwoFactorFirewallContext::class);
        $this->twoFactorFirewallContext
            ->expects($this->any())
            ->method('getFirewallConfig')
            ->with(self::FIREWALL_NAME)
            ->willReturn($this->firewallConfig);

        $this->initControllerWithTrustedFeature(true);
    }

    private function initControllerWithTrustedFeature(bool $trustedFeature): void
    {
        $this->controller = new FormController($this->tokenStorage, $this->providerRegistry, $this->twoFactorFirewallContext, $trustedFeature);
    }

    private function stubFirewallIsMultiFactor(bool $isMultiFactor): void
    {
        $this->firewallConfig
            ->expects($this->any())
            ->method('isMultiFactor')
            ->willReturn($isMultiFactor);
    }

    private function stubTokenStorageHasToken(TokenInterface $token): void
    {
        $this->tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);
    }

    private function stubTokenStorageHasTwoFactorToken(array $providers = ['provider1', 'provider2']): void
    {
        $this->twoFactorToken = $this->createMock(TwoFactorToken::class);
        $this->twoFactorToken
            ->expects($this->any())
            ->method('getCurrentTwoFactorProvider')
            ->willReturn(self::CURRENT_TWO_FACTOR_PROVIDER);

        $this->twoFactorToken
            ->expects($this->any())
            ->method('getTwoFactorProviders')
            ->willReturn($providers);

        $this->twoFactorToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->willReturn(self::FIREWALL_NAME);

        $this->stubTokenStorageHasToken($this->twoFactorToken);
    }

    private function stubRequestParameters(array $params): void
    {
        $this->request
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function (string $paramName) use ($params) {
                return $params[$paramName] ?? null;
            });
    }

    private function stubSessionHasException(\Exception $exception): void
    {
        $this->session
            ->expects($this->any())
            ->method('get')
            ->with(Security::AUTHENTICATION_ERROR)
            ->willReturn($exception);
    }

    private function assertTemplateVars(callable $callback): void
    {
        $this->formRenderer
            ->expects($this->once())
            ->method('renderForm')
            ->with($this->anything(), $this->callback($callback));
    }

    private function assertTemplateVarsHaveAuthenticationError($error, $errorData): void
    {
        $this->assertTemplateVars(function (array $templateVars) use ($error, $errorData) {
            $this->assertArrayHasKey('authenticationError', $templateVars);
            $this->assertArrayHasKey('authenticationErrorData', $templateVars);

            $this->assertEquals($error, $templateVars['authenticationError']);
            $this->assertEquals($errorData, $templateVars['authenticationErrorData']);

            return true;
        });
    }

    /**
     * @test
     */
    public function form_noTwoFactorToken_throwAccessDeniedException()
    {
        $this->stubTokenStorageHasToken($this->createMock(TokenInterface::class));

        $this->expectException(AccessDeniedException::class);
        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_setPreferredProvider_switchCurrentProvider()
    {
        $this->stubTokenStorageHasTwoFactorToken();
        $this->stubRequestParameters(['preferProvider' => 'provider2']);

        $this->twoFactorToken
            ->expects($this->once())
            ->method('preferTwoFactorProvider')
            ->with('provider2');

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_hasAuthenticationError_passErrorToRenderer()
    {
        $exception = new TwoFactorProviderNotFoundException('Authentication exception message');
        $exception->setProvider('unknownProvider');

        $this->stubTokenStorageHasTwoFactorToken();
        $this->stubSessionHasException($exception);

        $this->assertTemplateVarsHaveAuthenticationError(
            TwoFactorProviderNotFoundException::MESSAGE_KEY,
            ['{{ provider }}' => 'unknownProvider']
        );

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_hasOtherError_notPassErrorToRenderer()
    {
        $this->stubTokenStorageHasTwoFactorToken();
        $this->stubSessionHasException(new \Exception('Exception message'));

        $this->assertTemplateVarsHaveAuthenticationError(null, null);

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_multiFactorFirewallTwoProviders_displayTrustedOptionFalse()
    {
        $this->stubFirewallIsMultiFactor(true);
        $this->stubTokenStorageHasTwoFactorToken(['provider1', 'provider2']);

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertFalse($templateVars['displayTrustedOption']);

            return true;
        });

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_multiFactorFirewallOneProviderLeft_displayTrustedOptionTrue()
    {
        $this->stubFirewallIsMultiFactor(true);
        $this->stubTokenStorageHasTwoFactorToken(['provider1']);

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertTrue($templateVars['displayTrustedOption']);

            return true;
        });

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_notMultiFactorFirewallTwoProviders_displayTrustedOptionTrue()
    {
        $this->stubFirewallIsMultiFactor(false);
        $this->stubTokenStorageHasTwoFactorToken(['provider1', 'provider2']);

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertTrue($templateVars['displayTrustedOption']);

            return true;
        });

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_trustedDisabledMultiFactorFirewallOneProviderLeft_displayTrustedOptionFalse()
    {
        $this->initControllerWithTrustedFeature(false);
        $this->stubFirewallIsMultiFactor(true);
        $this->stubTokenStorageHasTwoFactorToken(['provider1']);

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertFalse($templateVars['displayTrustedOption']);

            return true;
        });

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_trustedDisabledNotMultiFactorFirewallTwoProviders_displayTrustedOptionFalse()
    {
        $this->initControllerWithTrustedFeature(false);
        $this->stubFirewallIsMultiFactor(false);
        $this->stubTokenStorageHasTwoFactorToken(['provider1', 'provider2']);

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertFalse($templateVars['displayTrustedOption']);

            return true;
        });

        $this->controller->form($this->request);
    }

    /**
     * @test
     */
    public function form_renderForm_renderTemplateWithTemplateVars()
    {
        $this->stubTokenStorageHasTwoFactorToken();

        $this->assertTemplateVars(function (array $templateVars) {
            $this->assertArrayHasKey('twoFactorProvider', $templateVars);
            $this->assertArrayHasKey('availableTwoFactorProviders', $templateVars);
            $this->assertArrayHasKey('authenticationError', $templateVars);
            $this->assertArrayHasKey('authenticationErrorData', $templateVars);
            $this->assertArrayHasKey('displayTrustedOption', $templateVars);
            $this->assertArrayHasKey('authCodeParameterName', $templateVars);
            $this->assertArrayHasKey('trustedParameterName', $templateVars);

            $this->assertEquals(self::CURRENT_TWO_FACTOR_PROVIDER, $templateVars['twoFactorProvider']);
            $this->assertEquals(['provider1', 'provider2'], $templateVars['availableTwoFactorProviders']);
            $this->assertEquals(self::AUTH_CODE_PARAM_NAME, $templateVars['authCodeParameterName']);
            $this->assertEquals(self::TRUSTED_PARAM_NAME, $templateVars['trustedParameterName']);

            return true;
        });

        $this->controller->form($this->request);
    }
}
