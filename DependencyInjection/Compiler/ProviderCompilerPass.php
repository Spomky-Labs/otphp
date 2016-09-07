<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ProviderCompilerPass implements CompilerPassInterface
{
    /**
     * Collect registered two-factor providers and register them.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheb_two_factor.provider_registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('scheb_two_factor.provider_registry');
        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.provider');
        $references = array();
        $providerNames = array();
        foreach ($taggedServices as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new InvalidArgumentException('Tag "scheb_two_factor.provider" requires attribute "alias" to be set.');
            }
            $name = $attributes[0]['alias'];
            $references[$name] = new Reference($id);
            $providerNames[] = $name;
        }
        $registryDefinition->replaceArgument(3, $references);
    }
}
