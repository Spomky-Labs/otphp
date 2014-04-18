<?php
namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scheb_two_factor');

        $rootNode
            ->children()
                ->arrayNode("email")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("mailer")->defaultNull()->end()
                        ->scalarNode("sender_email")->defaultValue("no-reply@example.com")->end()
                        ->scalarNode("template")->defaultValue("SchebTwoFactorBundle:Authentication:form.html.twig")->end()
                        ->scalarNode("digits")->defaultValue(4)->end()
                    ->end()
                ->end()
                ->arrayNode("google")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("server_name")->defaultNull()->end()
                        ->scalarNode("template")->defaultValue("SchebTwoFactorBundle:Authentication:form.html.twig")->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
