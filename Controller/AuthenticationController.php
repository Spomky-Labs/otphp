<?php
namespace Scheb\TwoFactorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class AuthenticationController extends Controller {

    private const FORM_DEFAULT_TEMPLATE = "@SchebTwoFactor/Authentication/form.html.twig";

    public function formAction(Request $request): Response
    {
        $authException = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        $authError = $authException ? $authException->getMessage() : '';

        // TODO: get configured template for the current authentication method
        $authProvider = 'google';
        $template = $this->getTemplate($authProvider);
        return $this->renderAuthenticationForm($template, [
            'authenticationProvider' => $authProvider,
            'authError' => $authError,
            'useTrustedOption' => true, // TODO
        ]);
    }

    protected function getTemplate(string $authenticationProvider): string {
        switch ($authenticationProvider) {
            case 'google':
                return $this->getParameter('scheb_two_factor.google.template') ?? self::FORM_DEFAULT_TEMPLATE;
            case 'email':
                return $this->getParameter('scheb_two_factor.email.template') ?? self::FORM_DEFAULT_TEMPLATE;
            default:
                return self::FORM_DEFAULT_TEMPLATE;
        }
    }

    protected function renderAuthenticationForm($template, array $parameters): Response {
        return $this->render($template, $parameters);
    }
}
