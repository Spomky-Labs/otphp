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
                ->scalarNode("model_manager_name")->defaultNull()->end()
                ->arrayNode("trusted_computer")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("enabled")->defaultFalse()->end()
                        ->scalarNode("cookie_name")->defaultValue("trusted_computer")->end()
                        ->scalarNode("cookie_lifetime")->defaultValue(60*24*3600)->end()
                    ->end()
                ->end()
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
