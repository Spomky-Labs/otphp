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
            ->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->with($this->logicalOr(
                $this->isInstanceOf('Scheb\TwoFactorBundle\DependencyInjection\Compiler\FirewallCompilerPass'),
                $this->isInstanceOf('Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass')
            ));

        //Expect register authentication provider factory
        $securityExtension = $this->createMock('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension');
        $containerBuilder
            ->expects($this->once())
            ->method('getExtension')
            ->with('security')
            ->willReturn($securityExtension);
        $securityExtension
            ->expects($this->once())
            ->method('addSecurityListenerFactory')
            ->with($this->isInstanceOf('\Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory'));

        $bundle = new SchebTwoFactorBundle();
        $bundle->build($containerBuilder);
    }
}
