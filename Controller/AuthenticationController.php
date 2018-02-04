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
        $template = $this->getParameter("scheb_two_factor.google.template") ?? self::FORM_DEFAULT_TEMPLATE;
        return $this->render($template, [
            'authenticationProvider' => $authProvider,
            'authError' => $authError,
            'useTrustedOption' => true, // TODO
        ]);
    }
}
