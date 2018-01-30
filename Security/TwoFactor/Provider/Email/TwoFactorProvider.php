<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var CodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * @var CodeValidatorInterface
     */
    private $authenticator;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var string
     */
    private $authCodeParameter;

    public function __construct(CodeGeneratorInterface $codeGenerator, CodeValidatorInterface $authenticator, Renderer $renderer, string $authCodeParameter)
    {
        $this->codeGenerator = $codeGenerator;
        $this->authenticator = $authenticator;
        $this->renderer = $renderer;
        $this->authCodeParameter = $authCodeParameter;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
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

    public function requestAuthenticationCode(AuthenticationContextInterface $context): ?Response
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();

        // Display and process form
        $authCode = $request->get($this->authCodeParameter);
        if ($authCode !== null) {
            if ($this->authenticator->checkCode($user, $authCode)) {
                $context->setAuthenticated(true);

                return new RedirectResponse($request->getUri());
            }

            $session->getFlashBag()->set('two_factor', 'scheb_two_factor.code_invalid');
        }

        // Force authentication code dialog
        return $this->renderer->render($context);
    }
}
