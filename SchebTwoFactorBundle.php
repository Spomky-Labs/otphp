<?php

namespace Scheb\TwoFactorBundle;

use Scheb\TwoFactorBundle\Security\TwoFactorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;

class SchebTwoFactorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Add compiler pass to register two-factor providers
        $container->addCompilerPass(new ProviderCompilerPass());

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TwoFactorFactory());
    }
}
