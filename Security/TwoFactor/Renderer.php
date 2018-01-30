<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class Renderer
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    private $formTemplate;

    public function __construct(EngineInterface $templating, string $formTemplate)
    {
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
    }

    public function render(AuthenticationContextInterface $context): Response {
        return $this->templating->renderResponse($this->formTemplate, [
            'useTrustedOption' => $context->useTrustedOption(),
        ]);
    }
}
