<?php

namespace Scheb\TwoFactorBundle\Tests;

use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SchebTwoFactorBundleTest extends TestCase
{
    /**
     * @test
     */
    public function build_initializeBundle_addCompilerPass()
    {
        $containerBuilder = new ContainerBuilderMock();
        $bundle = new SchebTwoFactorBundle();
        $bundle->build($containerBuilder);

        $this->assertCount(1, $containerBuilder->passes);
        $this->assertInstanceOf('Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass', $containerBuilder->passes[0]);
    }
}

class ContainerBuilderMock extends ContainerBuilder
{
    public $passes = array();

    public function addCompilerPass(CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, $priority = 0)
    {
        $this->passes[] = $pass;

        return parent::addCompilerPass($pass, $type, $priority);
    }
}
