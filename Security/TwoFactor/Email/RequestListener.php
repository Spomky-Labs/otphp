<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Email;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class RequestListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Email\AuthCodeManager $codeManager
     */
    private $codeManager;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     */
    private $securityContext;

    /**
     *
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    private $templating;

    /**
     * @var string $formTemplate
     */
    private $formTemplate;

    /**
     * Construct a request listener
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Email\AuthCodeManager $helper
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param string $formTemplate
     */
    public function __construct(AuthCodeManager $codeManager, SecurityContextInterface $securityContext, EngineInterface $templating, $formTemplate)
    {
        $this->codeManager = $codeManager;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
    }

    /**
     * Listen for request events
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $token = $this->securityContext->getToken();
        if (! $token) {
            return;
        }
        if (! $token instanceof UsernamePasswordToken) {
            return;
        }

        $sessionFlag = sprintf('two_factor_email_%s_%s', $token->getProviderKey(), $token->getUsername());
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();
        $user = $this->securityContext->getToken()->getUser();

        // Check if user has to do two-factor authentication
        if (! $session->has($sessionFlag)) {
            return;
        }
        if ($session->get($sessionFlag) === true) {
            return;
        }

        if ($request->getMethod() == 'POST') {

            // Check the authentication code
            if ($this->codeManager->checkCode($user, $request->get('_auth_code')) == true) {

                // Flag authentication complete
                $session->set($sessionFlag, true);

                // Redirect
                $redirect = new RedirectResponse($request->getUri());
                $event->setResponse($redirect);
                return;
            } else {
                $session->getFlashBag()->set("two_factor", "scheb_two_factor.code_invalid");
            }
        }

        // Force authentication code dialog
        $response = $this->templating->renderResponse($this->formTemplate);
        $event->setResponse($response);
    }
}

