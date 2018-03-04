<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\DefaultTwoFactorFormRenderer;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class DefaultTwoFactorFormRendererTest extends TestCase
{
    const TEMPLATE = 'template.html.twig';

    /**
     * @var MockObject|Environment
     */
    private $twig;

    /**
     * @var DefaultTwoFactorFormRenderer
     */
    private $formRender;

    protected function setUp()
    {
        $this->twig = $this->createMock(Environment::class);
        $this->formRender = new DefaultTwoFactorFormRenderer($this->twig, self::TEMPLATE);
    }

    /**
     * @test
     */
    public function renderForm_templateVarsGiven_createResponseWithRenderedForm()
    {
        $request = $this->createMock(Request::class);
        $templateVars = ['var1' => 'value1', 'var2' => 'value2'];

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with(self::TEMPLATE, $templateVars)
            ->willReturn('<RenderedForm>');

        $returnValue = $this->formRender->renderForm($request, $templateVars);
        $this->assertEquals('<RenderedForm>', $returnValue->getContent());
    }
}
