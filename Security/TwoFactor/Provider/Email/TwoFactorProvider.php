<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class TwoFactorProvider implements TwoFactorProviderInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\AuthCodeManager $codeManager
     */
    private $codeManager;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    private $templating;

    /**
     * @var string $formTemplate
     */
    private $formTemplate;

    /**
     * Construct provider for email authentication
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\AuthCodeManager $codeManager
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param string $formTemplate
     */
    public function __construct(AuthCodeManager $codeManager, EngineInterface $templating, $formTemplate)
    {
        $this->codeManager = $codeManager;
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
    }

    /**
     * Begin email authentication process
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return boolean
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();
        if ($user instanceof TwoFactorInterface && $user->isEmailAuthEnabled())
        {
            // Generate and send a new security code
            $this->codeManager->generateAndSend($user);
            return true;
        }
        return false;
    }

    /**
     * Ask for email authentication code
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();

        // Display and process form
        if ($request->getMethod() == 'POST') {
            if ($this->codeManager->checkCode($user, $request->get('_auth_code')) == true) {
                $context->setAuthenticated(true);
                return new RedirectResponse($request->getUri());
            } else {
                $session->getFlashBag()->set("two_factor", "scheb_two_factor.code_invalid");
            }
        }

        // Force authentication code dialog
        return $this->templating->renderResponse($this->formTemplate, array(
            'useTrustedOption' => $context->useTrustedOption()
        ));
    }

}