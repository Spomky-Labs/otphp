<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\InvalidTwoFactorCodeException;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\TwoFactorProviderNotFoundException;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorAuthenticationProvider implements AuthenticationProviderInterface
{
    private const DEFAULT_OPTIONS = [
        'multi_factor' => TwoFactorFactory::DEFAULT_MULTI_FACTOR,
    ];

    /**
     * @var TwoFactorProviderInterface[]
     */
    private $providers;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var array
     */
    private $options;

    /**
     * @var BackupCodeManagerInterface
     */
    private $backupCodeManager;

    public function __construct(iterable $providers, string $firewallName, array $options, BackupCodeManagerInterface $backupCodeManager) {
        $this->providers = $providers;
        $this->firewallName = $firewallName;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
        $this->backupCodeManager = $backupCodeManager;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken && $this->firewallName === $token->getProviderKey();
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

        $providerName = $token->getCurrentTwoFactorProvider();
        if ($this->isValidAuthenticationCode($providerName, $token)) {
            $token->setTwoFactorProviderComplete($providerName);
            if ($this->isAuthenticationComplete($token)) {
                $token = $token->getAuthenticatedToken(); // Authentication complete, unwrap the token
            }

            return $token;
        } else {
            throw new InvalidTwoFactorCodeException('Invalid two-factor authentication code.');
        }
    }

    private function isValidAuthenticationCode(string $providerName, TwoFactorToken $token): bool
    {
        $user = $token->getUser();
        $authenticationCode = $token->getCredentials();

        if ($this->isValidTwoFactorCode($user, $providerName, $authenticationCode)) {
            return true;
        }
        if ($this->isValidBackupCode($user, $authenticationCode)) {
            return true;
        }

        return false;
    }

    private function isValidTwoFactorCode($user, string $providerName, string $authenticationCode): bool
    {
        foreach ($this->providers as $name => $authenticationProvider) {
            if ($providerName === $name) {
                return $authenticationProvider->validateAuthenticationCode($user, $authenticationCode);
            }
        }

        $exception = new TwoFactorProviderNotFoundException('Two-factor provider "' . $providerName. '" not found.');
        $exception->setProvider($providerName);
        throw $exception;
    }

    private function isValidBackupCode($user, string $authenticationCode): bool
    {
        if ($this->backupCodeManager->isBackupCode($user, $authenticationCode)) {
            $this->backupCodeManager->invalidateBackupCode($user, $authenticationCode);
            return true;
        }

        return false;
    }

    private function isAuthenticationComplete(TwoFactorToken $token): bool
    {
        return !$this->options['multi_factor'] || $token->allTwoFactorProvidersAuthenticated();
    }
}
