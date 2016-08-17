<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Scheb\TwoFactorBundle\Tests\TestCase;

class RendererTest extends TestCase
{
    /**
     * @test
     */
    public function render_returnResponse()
    {
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating
            ->expects($this->any())
            ->method('renderResponse')
            ->willReturn($response)
        ;
        $context = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface');

        $renderer = new Renderer($templating, 'AcmeTestBundle:Test:auth.html.twig');

        $this->assertEquals($response, $renderer->render($context));
    }
}
