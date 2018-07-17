<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect registered two-factor firewall configs and add them to the context.
 */
class TwoFactorFirewallConfigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheb_two_factor.firewall_context')) {
            return;
        }

        $firewallContextDefinition = $container->getDefinition('scheb_two_factor.firewall_context');
        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.firewall_config');

        $references = [];
        foreach ($taggedServices as $id => $attributes) {
            if (!isset($attributes[0]['firewall'])) {
                throw new InvalidArgumentException('Tag "scheb_two_factor.firewall_config" requires attribute "firewall" to be set.');
            }
            $name = $attributes[0]['firewall'];
            $references[$name] = new Reference($id);
        }

        $firewallContextDefinition->replaceArgument(0, $references);
    }
}
