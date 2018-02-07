<?php
namespace Scheb\TwoFactorBundle\Security\Http\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class DefaultAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

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
    private $firewallName;

    /**
     * @var array
     */
    private $defaultOptions = [
        'always_use_default_target_path' => false,
        'default_target_path' => '/',
    ];

    public function __construct(HttpUtils $httpUtils, string $firewallName, array $options = [])
    {
        $this->httpUtils = $httpUtils;
        $this->firewallName = $firewallName;
        $this->options = array_merge($this->defaultOptions, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->remove(Security::AUTHENTICATION_ERROR);

        return $this->httpUtils->createRedirectResponse($request, $this->determineRedirectTargetUrl($request));
    }

    private function determineRedirectTargetUrl(Request $request): string
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        $session = $request->getSession();
        if ($targetUrl = $this->getTargetPath($session, $this->firewallName)) {
            $this->removeTargetPath($session, $this->firewallName);

            return $targetUrl;
        }

        return $this->options['default_target_path'];
    }
}
