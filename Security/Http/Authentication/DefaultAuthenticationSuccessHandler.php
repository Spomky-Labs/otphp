<?php
namespace Scheb\TwoFactorBundle\Security\Http\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class DefaultAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var array
     */
    private $defaultOptions = array(
        'always_use_default_target_path' => false,
        'default_target_path' => '/',
    );

    /**
     * @param HttpUtils $httpUtils
     * @param string $providerKey
     * @param array $options Options for processing a successful authentication attempt
     */
    public function __construct(HttpUtils $httpUtils, $providerKey, array $options = array())
    {
        $this->httpUtils = $httpUtils;
        $this->providerKey = $providerKey;
        $this->setOptions($options);
    }

    /**
     * @param array $options An array of options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->remove(Security::AUTHENTICATION_ERROR);

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    private function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        if ($targetUrl = $request->getSession()->get('_security.' . $this->providerKey . '.target_path')) {
            $request->getSession()->remove('_security.' . $this->providerKey . '.target_path');

            return $targetUrl;
        }

        return $this->options['default_target_path'];
    }
}
