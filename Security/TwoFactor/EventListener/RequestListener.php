<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;

class RequestListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     */
    private $authHandler;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    private $securityContext;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     * @param \Symfony\Component\Security\Core\SecurityContextInterface                $securityContext
     * @param array                                                                    $supportedTokens
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, SecurityContextInterface $securityContext, array $supportedTokens)
    {
        $this->authHandler = $authHandler;
        $this->securityContext = $securityContext;
        $this->supportedTokens = $supportedTokens;
    }

    /**
     * Listen for request events
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->securityContext->getToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two factor provider
        // Providers can create a response object
        $context = new AuthenticationContext($request, $token);
        $response = $this->authHandler->requestAuthenticationCode($context);

        // Set the response (if there is one)
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /**
     * Check if the token class is supported
     *
     * @param  mixed   $token
     * @return boolean
     */
    private function isTokenSupported($token)
    {
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
