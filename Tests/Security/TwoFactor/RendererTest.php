<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class RendererTest extends TestCase
{
    /**
     * @test
     */
    public function render_returnResponse()
    {
        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects($this->any())
            ->method('renderResponse')
            ->willReturn($response)
        ;
        $context = $this->createMock(AuthenticationContextInterface::class);

        $renderer = new Renderer($templating, 'AcmeTestBundle:Test:auth.html.twig');

        $this->assertEquals($response, $renderer->render($context));
    }
}
