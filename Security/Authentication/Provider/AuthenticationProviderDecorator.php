<?php

namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationProviderDecorator implements AuthenticationProviderInterface
{
    /**
     * @var AuthenticationProviderInterface
     */
    private $decoratedAuthenticationProvider;

    /**
     * @var AuthenticationHandlerInterface
     */
    private $twoFactorAuthenticationHandler;

    /**
     * @var AuthenticationContextFactoryInterface
     */
    private $authenticationContextFactory;

    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        AuthenticationProviderInterface $decoratedAuthenticationProvider,
        AuthenticationHandlerInterface $twoFactorAuthenticationHandler,
        AuthenticationContextFactoryInterface $authenticationContextFactory,
        FirewallMap $firewallMap,
        RequestStack $requestStack
    ) {
        $this->decoratedAuthenticationProvider = $decoratedAuthenticationProvider;
        $this->twoFactorAuthenticationHandler = $twoFactorAuthenticationHandler;
        $this->authenticationContextFactory = $authenticationContextFactory;
        $this->firewallMap = $firewallMap;
        $this->requestStack = $requestStack;
    }

    public function supports(TokenInterface $token)
    {
        return $this->decoratedAuthenticationProvider->supports($token);
    }

    public function authenticate(TokenInterface $token)
    {
        $token = $this->decoratedAuthenticationProvider->authenticate($token);

        // AnonymousToken and TwoFactorToken can be ignored
        // in case of Guard, it can return null due to having multiple guard authenticators
        if ($token instanceof AnonymousToken || $token instanceof TwoFactorToken || null === $token) {
            return $token;
        }

        $request = $this->requestStack->getMasterRequest();
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);

        if (!in_array(TwoFactorFactory::AUTHENTICATION_PROVIDER_KEY, $firewallConfig->getListeners())) {
            return $token; // This firewall doesn't support two-factor authentication
        }

        $context = $this->authenticationContextFactory->create($request, $token, $firewallConfig->getName());

        return $this->twoFactorAuthenticationHandler->beginTwoFactorAuthentication($context);
    }
}
