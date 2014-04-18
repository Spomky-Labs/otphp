<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class TwoFactorProvider implements TwoFactorProviderInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator $authenticator
     */
    private $authenticator;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    private $templating;

    /**
     * @var string $formTemplate
     */
    private $formTemplate;

    /**
     * Construct provider for Google authentication
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator $helper
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param string $formTemplate
     */
    public function __construct(GoogleAuthenticator $authenticator, EngineInterface $templating, $formTemplate)
    {
        $this->authenticator = $authenticator;
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
    }

    /**
     * Begin Google authentication process
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return boolean
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();
        if (! $user instanceof TwoFactorInterface) {
            return false;
        }
        if (! $user->getGoogleAuthenticatorSecret()) {
            return false;
        }

        return true;
    }

    /**
     * Ask for Google authentication code
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
            if ($this->authenticator->checkCode($user, $request->get('_auth_code')) == true) {
                $context->setAuthenticated(true);
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
        return sprintf('two_factor_google_%s_%s', $token->getProviderKey(), $token->getUsername());
    }
}
