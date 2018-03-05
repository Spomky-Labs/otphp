<?php

namespace Scheb\TwoFactorBundle\Controller;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\UnknownTwoFactorProviderException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class FormController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TwoFactorProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var TwoFactorFirewallContext
     */
    private $twoFactorFirewallContext;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TwoFactorProviderRegistry $providerRegistry,
        TwoFactorFirewallContext $twoFactorFirewallContext
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->providerRegistry = $providerRegistry;
        $this->twoFactorFirewallContext = $twoFactorFirewallContext;
    }

    public function form(Request $request): Response
    {
        $token = $this->getTwoFactorToken();
        $this->setPreferredProvider($request, $token);

        $providerName = $token->getCurrentTwoFactorProvider();
        $renderer = $this->providerRegistry->getProvider($providerName)->getFormRenderer();
        $templateVars = $this->getTemplateVars($request, $token);

        return $renderer->renderForm($request, $templateVars);
    }

    protected function getTwoFactorToken(): TwoFactorToken
    {
        $token = $this->tokenStorage->getToken();
        if (!($token instanceof TwoFactorToken)) {
            throw new AccessDeniedException('User is not in a two-factor authentication process.');
        }

        return $token;
    }

    protected function setPreferredProvider(Request $request, TwoFactorToken $token): void
    {
        $preferredProvider = $request->get('preferProvider');
        if ($preferredProvider) {
            try {
                $token->preferTwoFactorProvider($preferredProvider);
            } catch (UnknownTwoFactorProviderException $e) {
            }
        }
    }

    protected function getTemplateVars(Request $request, TwoFactorToken $token): array
    {
        $config = $this->twoFactorFirewallContext->getFirewallConfig($token->getProviderKey());
        $pendingTwoFactorProviders = $token->getTwoFactorProviders();
        $displayTrustedOption = !$config->isMultiFactor() || 1 === count($pendingTwoFactorProviders);

        return [
            'twoFactorProvider' => $token->getCurrentTwoFactorProvider(),
            'availableTwoFactorProviders' => $pendingTwoFactorProviders,
            'authError' => $this->getLastAuthenticationError($request->getSession()),
            'displayTrustedOption' => $displayTrustedOption,
            'authCodeParameterName' => $config->getAuthCodeParameterName(),
            'trustedParameterName' => $config->getTrustedParameterName(),
        ];
    }

    protected function getLastAuthenticationError(SessionInterface $session): ?string
    {
        $authException = $session->get(Security::AUTHENTICATION_ERROR);
        if (!($authException instanceof AuthenticationException)) {
            return null; // The value does not come from the security component.
        }

        $session->remove(Security::AUTHENTICATION_ERROR);

        return $authException->getMessage() ?? null;
    }
}
