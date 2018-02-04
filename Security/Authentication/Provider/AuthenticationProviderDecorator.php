<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $providerKey;

    public function __construct(
        AuthenticationProviderInterface $decoratedAuthenticationProvider,
        AuthenticationHandlerInterface $twoFactorAuthenticationHandler,
        AuthenticationContextFactoryInterface $authenticationContextFactory,
        RequestStack $requestStack,
        string $providerKey
    )
    {
        $this->decoratedAuthenticationProvider = $decoratedAuthenticationProvider;
        $this->twoFactorAuthenticationHandler = $twoFactorAuthenticationHandler;
        $this->authenticationContextFactory = $authenticationContextFactory;
        $this->requestStack = $requestStack;
        $this->providerKey = $providerKey;
    }

    public function supports(TokenInterface $token)
    {
        return $this->decoratedAuthenticationProvider->supports($token);
    }

    public function authenticate(TokenInterface $token)
    {
        $token = $this->decoratedAuthenticationProvider->authenticate($token);

        // AnonymousToken and TwoFactorToken can be ignored
        if ($token instanceof AnonymousToken || $token instanceof TwoFactorToken) {
            return $token;
        }

        $request = $this->requestStack->getMasterRequest();
        $context = $this->authenticationContextFactory->create($request, $token, $this->providerKey);
        return $this->twoFactorAuthenticationHandler->beginTwoFactorAuthentication($context);
    }
}
