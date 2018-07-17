<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\AuthenticationProviderDecoratorCompilerPass;
use Scheb\TwoFactorBundle\DependencyInjection\Compiler\TwoFactorFirewallConfigCompilerPass;
use Scheb\TwoFactorBundle\DependencyInjection\Compiler\TwoFactorProviderCompilerPass;
use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SchebTwoFactorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AuthenticationProviderDecoratorCompilerPass());
        $container->addCompilerPass(new TwoFactorProviderCompilerPass());
        $container->addCompilerPass(new TwoFactorFirewallConfigCompilerPass());

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new TwoFactorFactory());
    }
}
