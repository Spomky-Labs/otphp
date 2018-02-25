<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManager;
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

    /**
     * @var TrustedDeviceManager
     */
    private $trustedDeviceManager;

    /**
     * @var array
     */
    private $options;

    public function __construct(TrustedDeviceManager $trustedDeviceManager, iterable $providers, string $firewallName, array $options) {
        $this->trustedDeviceManager = $trustedDeviceManager;
        $this->providers = $providers;
        $this->firewallName = $firewallName;
        $this->options = array_merge([
            'trusted_parameter_name' => '_trusted',
        ], $options);
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
            $this->trustedDeviceManager->addTrustedDevice($authenticatedToken->getUser(), $this->firewallName);
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
