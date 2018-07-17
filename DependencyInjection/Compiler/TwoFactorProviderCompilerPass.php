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

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect registered two-factor providers and register them.
 */
class TwoFactorProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheb_two_factor.provider_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('scheb_two_factor.provider_registry');
        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.provider');

        $references = [];
        foreach ($taggedServices as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new InvalidArgumentException('Tag "scheb_two_factor.provider" requires attribute "alias" to be set.');
            }
            $name = $attributes[0]['alias'];
            $references[$name] = new Reference($id);
        }

        $registryDefinition->replaceArgument(0, new IteratorArgument($references));
    }
}
