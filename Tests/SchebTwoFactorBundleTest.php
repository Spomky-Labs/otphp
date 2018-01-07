<?php

namespace Scheb\TwoFactorBundle\Tests;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SchebTwoFactorBundleTest extends TestCase
{
    /**
     * @test
     */
    public function build_initializeBundle_addCompilerPass()
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new SchebTwoFactorBundle();
        $bundle->build($containerBuilder);

        $foundIt = false;
        foreach ($containerBuilder->getCompilerPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ProviderCompilerPass) {
                $foundIt = true;
                break;
            }
        }

        $this->assertTrue($foundIt);
    }
}
