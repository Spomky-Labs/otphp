<?php
namespace Scheb\TwoFactorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class AuthController extends Controller {

    public function authAction(Request $request)
    {
        $authException = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        $authError = $authException ? $authException->getMessage() : '';

        return new Response($authError. ' <a href="' . $this->generateUrl('_security_logout') . '">Logout</a> <a href="' . $this->generateUrl('2fa_login_check') . '?_auth_code=1">Send Correct 2FA</a> <a href="' . $this->generateUrl('2fa_login_check') . '?_auth_code=0">Send Wrong 2FA</a>');
    }
}
