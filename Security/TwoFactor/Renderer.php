<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class Renderer
{
    private $templating;
    private $formTemplate;

    public function __construct(EngineInterface $templating, $formTemplate)
    {
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
    }

    public function render(AuthenticationContextInterface $context) {
        return $this->templating->renderResponse($this->formTemplate, array(
            'useTrustedOption' => $context->useTrustedOption(),
        ));
    }
}
