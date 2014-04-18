<?php
namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPass implements CompilerPassInterface
{

    /**
     * Collect registered two factor providers and register them
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (! $container->hasDefinition("scheb_two_factor.provider")) {
            return;
        }

        $definition = $container->getDefinition('scheb_two_factor.provider');
        $taggedServices = $container->findTaggedServiceIds('scheb_two_factor.provider');
        $references = array();
        foreach ($taggedServices as $id => $attributes) {
            $references[] = new Reference($id);
        }
        $definition->replaceArgument(0, $references);
    }
}