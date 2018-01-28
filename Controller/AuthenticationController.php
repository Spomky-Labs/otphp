<?php
namespace Scheb\TwoFactorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class AuthenticationController extends Controller {

    public function formAction(Request $request)
    {
        $authException = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        $authError = $authException ? $authException->getMessage() : '';

        // TODO: get configured template for the current authentication method
        $template = $this->getParameter("scheb_two_factor.google.template") ?? "@SchebTwoFactor/Authentication/form.html.twig";
        return $this->render($template, [
            'authError' => $authError,
        ]);
    }
}
