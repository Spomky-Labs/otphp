<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collect registered two-factor providers and register them.
 */
class ProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scheb_two_factor.provider_handler')) {
            return;
        }

        $twoFactorProviderHandlerDefinition = $container->getDefinition('scheb_two_factor.provider_handler');
        $firewallAuthenticationProviderDefinition = $container->getDefinition('scheb_two_factor.security.authentication.provider');

        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.provider');
        $references = [];
        foreach ($taggedServices as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new InvalidArgumentException('Tag "scheb_two_factor.provider" requires attribute "alias" to be set.');
            }
            $name = $attributes[0]['alias'];
            $references[$name] = new Reference($id);
        }

        $iteratorArgument = new IteratorArgument($references);

        $twoFactorProviderHandlerDefinition->replaceArgument(0, $iteratorArgument);
        $firewallAuthenticationProviderDefinition->replaceArgument(1, $iteratorArgument);
    }
}
