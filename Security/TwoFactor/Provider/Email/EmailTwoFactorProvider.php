<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class EmailTwoFactorProvider implements TwoFactorProviderInterface
{
    /**
     * @var CodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * @var TwoFactorFormRendererInterface
     */
    private $formRenderer;

    public function __construct(CodeGeneratorInterface $codeGenerator, TwoFactorFormRendererInterface $formRenderer)
    {
        $this->codeGenerator = $codeGenerator;
        $this->formRenderer = $formRenderer;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        // Check if user can do email authentication
        $user = $context->getUser();
        if ($user instanceof TwoFactorInterface && $user->isEmailAuthEnabled()) {
            // Generate and send a new security code
            $this->codeGenerator->generateAndSend($user);

            return true;
        }

        return false;
    }

    public function validateAuthenticationCode($user, string $authenticationCode): bool
    {
        if (!($user instanceof TwoFactorInterface)) {
            return false;
        }

        return $user->getEmailAuthCode() === $authenticationCode;
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}
