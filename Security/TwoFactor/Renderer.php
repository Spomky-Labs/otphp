<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

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
        return $this->templating->renderResponse($this->formTemplate, [
            'useTrustedOption' => $context->useTrustedOption(),
        ]);
    }
}
