<?php
namespace Scheb\TwoFactorBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

class TwoFactorAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var string
     */
    private $authFormPath;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @param HttpUtils  $httpUtils  An HttpUtils instance
     * @param string     $loginPath  The path to the login form
     */
    public function __construct(HttpUtils $httpUtils, $loginPath)
    {
        $this->httpUtils = $httpUtils;
        $this->authFormPath = $loginPath;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->httpUtils->createRedirectResponse($request, $this->authFormPath);
    }

    /**
     * If current request already displays the entry point
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isAuthFormRequest(Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->authFormPath);
    }
}
