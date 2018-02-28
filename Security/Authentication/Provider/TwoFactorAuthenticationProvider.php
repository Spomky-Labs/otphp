<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var TwoFactorProviderInterface[]
     */
    private $providers;

    /**
     * @var string
     */
    private $firewallName;

    public function __construct(iterable $providers, string $firewallName) {
        $this->providers = $providers;
        $this->firewallName = $firewallName;
    }

    public function authenticate(TokenInterface $token)
    {
        /** @var TwoFactorToken $token */
        if (!$this->supports($token)) {
            return null;
        }

        // Keep unauthenticated TwoFactorToken with no credentials given
        if ($token->getCredentials() === null) {
            return $token;
        }

        if ($this->checkAuthenticationCode($token)) {
            $authenticatedToken = $token->getAuthenticatedToken();
//            $request->getSession()->remove(Security::AUTHENTICATION_ERROR);

            return $authenticatedToken;
        } else {
            throw new AuthenticationException('Invalid two-factor authentication code.');
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken && $this->firewallName === $token->getProviderKey();
    }

    private function checkAuthenticationCode(TwoFactorToken $token)
    {
        return $token->getCredentials() === '1';
//        $authenticationProvider = $this->getAuthenticationProvider();
//        return $authenticationProvider->validateAuthenticationCode($token->getUser(), $token->getCredentials());
//
//        // TODO: validate backup code, if everything else fails
//        if ($user instanceof BackupCodeInterface && $this->backupCodeComparator->checkCode($user, $code)) {
//            return true;
//        }
//
//        return $this->validator->checkCode($user, $code);
//    }
//
//    private function getAuthenticationProvider(): TwoFactorProviderInterface
//    {
//        foreach ($this->providers as $providerName => $provider) {
//
//        }
    }
}
