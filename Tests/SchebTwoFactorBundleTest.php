<?php

namespace Scheb\TwoFactorBundle\Tests;

use Scheb\TwoFactorBundle\SchebTwoFactorBundle;

class SchebTwoFactorBundleTest extends TestCase
{
    /**
     * @test
     */
    public function build_initializeBundle_addCompilerPass()
    {
        $containerBuilder = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        //Expect compiler pass to be added
        $containerBuilder
            ->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass'));

        $bundle = new SchebTwoFactorBundle();
        $bundle->build($containerBuilder);
    }
}
