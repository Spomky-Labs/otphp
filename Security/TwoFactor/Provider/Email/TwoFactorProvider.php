<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function beginAuthentication(Request $request, TokenInterface $token)
    {
        // Check if user can do email authentication
        $user = $token->getUser();
        if (! $user instanceof TwoFactorInterface) {
            return;
        }
        if (! $user->isEmailAuthEnabled()) {
            return;
        }

        // Set flag in the session
        $sessionFlag = $this->getSessionFlag($token);
        $session = $request->getSession();
        $session->set($sessionFlag, null);

        // Generate and send a new security code
        $this->codeManager->generateAndSend($user);
    }

    /**
     * Ask for email authentication code
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $session = $request->getSession();

        // Check if user has to do two-factor authentication
        $sessionFlag = $this->getSessionFlag($token);
        if (! $session->has($sessionFlag)) {
            return null;
        }
        if ($session->get($sessionFlag) === true) {
            return null;
        }

        // Display and process form
        if ($request->getMethod() == 'POST') {
            if ($this->codeManager->checkCode($user, $request->get('_auth_code')) == true) {

                // Flag authentication complete
                $session->set($sessionFlag, true);

                // Redirect to previous page
                return new RedirectResponse($request->getUri());
            } else {
                $session->getFlashBag()->set("two_factor", "scheb_two_factor.code_invalid");
            }
        }

        // Force authentication code dialog
        return $this->templating->renderResponse($this->formTemplate);
    }

    /**
     * Generate session token
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return string
     */
    protected function getSessionFlag($token)
    {
        return sprintf('two_factor_email_%s_%s', $token->getProviderKey(), $token->getUsername());
    }
}