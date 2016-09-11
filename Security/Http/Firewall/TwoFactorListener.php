<?php
namespace Scheb\TwoFactorBundle\Security\Http\Firewall;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\Http\EntryPoint\TwoFactorAuthenticationEntryPoint;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class TwoFactorListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var AuthenticationEntryPointInterface
     */
    private $entryPoint;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param TwoFactorAuthenticationEntryPoint $entryPoint
     * @param string $providerKey
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, TwoFactorAuthenticationEntryPoint $entryPoint, $providerKey)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->entryPoint = $entryPoint;
        $this->providerKey = $providerKey;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $currentToken = $this->tokenStorage->getToken();
        if (!($currentToken instanceof TwoFactorToken)) {
            return;
        }

        $request = $event->getRequest();
        $authCode = $request->get('_auth_code'); // TODO: configurable
        if ($authCode === null) {
            // Redirect to two-factor authentication form
            if (!$this->entryPoint->isAuthFormRequest($request)) {
                $response = $this->entryPoint->start($request);
                $event->setResponse($response);
            }
            return;
        }

        // Try two-factor authentication
        try {
            $token = new TwoFactorToken($currentToken->getAuthenticatedToken(), $authCode, $this->providerKey);
            $authenticatedToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authenticatedToken);
            return;
        } catch (AuthenticationException $failed) {
            // TODO: handle exception
        }
    }
}
