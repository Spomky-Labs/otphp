<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TwoFactorProvider implements TwoFactorProviderInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface $authenticator
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
     * @var string $authCodeParameter
     */
    private $authCodeParameter;

    /**
     * Construct provider for email authentication
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface  $codeGenerator
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface $authenticator
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface                                 $templating
     * @param string                                                                                     $formTemplate
     * @param string                                                                                     $authCodeParameter
     */
    public function __construct(CodeGeneratorInterface $codeGenerator, CodeValidatorInterface $authenticator, EngineInterface $templating, $formTemplate, $authCodeParameter)
    {
        $this->codeGenerator = $codeGenerator;
        $this->authenticator = $authenticator;
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
        $this->authCodeParameter = $authCodeParameter;
    }

    /**
     * Begin email authentication process
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return boolean
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();
        if ($user instanceof TwoFactorInterface && $user->isEmailAuthEnabled()) {
            // Generate and send a new security code
            $this->codeGenerator->generateAndSend($user);

            return true;
        }

        return false;
    }

    /**
     * Ask for email authentication code
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();

        // Display and process form
        if ($request->getMethod() == 'POST') {
            if ($this->authenticator->checkCode($user, $request->get($this->authCodeParameter)) == true) {
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
