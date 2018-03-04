<?php

namespace Scheb\TwoFactorBundle\Security\Http\Authentication;

use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class DefaultAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private const DEFAULT_OPTIONS = [
        'auth_form_path' => TwoFactorFactory::DEFAULT_AUTH_FORM_PATH,
    ];

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var array
     */
    private $options;

    public function __construct(HttpUtils $httpUtils, array $options = [])
    {
        $this->httpUtils = $httpUtils;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->options['auth_form_path']);
    }
}
