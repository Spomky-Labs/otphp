<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultTwoFactorFormRenderer implements TwoFactorFormRendererInterface
{
    /**
     * @var Environment
     */
    private $twigEnvironment;

    /**
     * @var string
     */
    private $template;

    public function __construct(Environment $twigRenderer, string $template)
    {
        $this->template = $template;
        $this->twigEnvironment = $twigRenderer;
    }

    public function renderForm(Request $request, array $templateVars): Response
    {
        $content = $this->twigEnvironment->render($this->template, $templateVars);
        $response = new Response();
        $response->setContent($content);

        return $response;
    }
}
