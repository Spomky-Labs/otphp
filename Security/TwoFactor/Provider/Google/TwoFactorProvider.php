<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProvider implements TwoFactorProviderInterface
{
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

    public function __construct(CodeValidatorInterface $authenticator, Renderer $renderer, $authCodeParameter)
    {
        $this->authenticator = $authenticator;
        $this->renderer = $renderer;
        $this->authCodeParameter = $authCodeParameter;
    }

    /**
     * Begin Google authentication process.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContextInterface $context)
    {
        // Check if user can do email authentication
        $user = $context->getUser();

        return $user instanceof TwoFactorInterface && $user->getGoogleAuthenticatorSecret();
    }

    /**
     * Ask for Google authentication code.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context)
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
