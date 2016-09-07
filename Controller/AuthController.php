<?php
namespace Scheb\TwoFactorBundle\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactorToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthController extends Controller {

    public function authAction()
    {
        /** @var TokenInterface $token */
        $token = $this->get('security.token_storage')->getToken();
        if (!($token instanceof TwoFactorToken)) {
            return new Response('Already authenticated');
        }

        return new Response('<a href="' . $this->generateUrl('_security_logout') . '">Logout</a> <a href="?_auth_code=1">Send 2FA</a>');
    }
}
