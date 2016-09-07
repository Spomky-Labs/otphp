<?php
namespace Scheb\TwoFactorBundle\Security;

use Scheb\TwoFactorBundle\Security\Http\EntryPoint\TwoFactorAuthenticationEntryPoint;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
     * @param TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param TwoFactorAuthenticationEntryPoint $entryPoint
     */
    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, TwoFactorAuthenticationEntryPoint $entryPoint)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->entryPoint = $entryPoint;
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
        $authCode = $request->get('_auth_code');
        if ($authCode !== null) {
//            try {
//                $token = new TwoFactorToken($currentToken->getAuthenticatedToken(), $authCode);
//                $authenticatedToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($currentToken->getAuthenticatedToken());
                return;
//            } catch (AuthenticationException $failed) {
//
//            }
        }

        if (!$this->entryPoint->isAuthFormRequest($request)) {
            $response = $this->entryPoint->start($request);
            $event->setResponse($response);
        }
    }
}
