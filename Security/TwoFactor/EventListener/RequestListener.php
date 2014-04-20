<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;

class RequestListener
{

    /**
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     */
    private $provider;

    /**
     *
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    private $securityContext;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    public function __construct(TwoFactorProvider $provider, SecurityContextInterface $securityContext)
    {
        $this->provider = $provider;
        $this->securityContext = $securityContext;
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
        if (! $token instanceof UsernamePasswordToken) {
            return;
        }

        // Forward to two factor provider
        // Providers can create a response object
        $response = $this->provider->requestAuthenticationCode($request, $token);

        // Set the response (if there is one)
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }
}
