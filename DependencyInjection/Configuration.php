<?php

namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scheb_two_factor');

        $rootNode
            ->children()
                ->scalarNode('persister')->defaultNull()->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
                ->arrayNode('trusted_computer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('cookie_name')->defaultValue('trusted_computer')->end()
                        ->scalarNode('cookie_lifetime')->defaultValue(60 * 24 * 3600)->end()
                        ->booleanNode('cookie_secure')->defaultValue(false)->end()
                        ->scalarNode('cookie_same_site')
                            ->defaultValue('lax')
                            ->validate()
                            ->ifNotInArray(array('lax', 'strict'))
                                ->thenInvalid('Invalid cookie same-site value %s, must be "lax" or "strict"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('exclude_pattern')->defaultNull()->end()
                ->arrayNode('parameter_names')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('auth_code')->defaultValue('_auth_code')->end()
                        ->scalarNode('trusted')->defaultValue('_trusted')->end()
                    ->end()
                ->end()
                ->arrayNode('email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('mailer')->defaultNull()->end()
                        ->scalarNode('sender_email')->defaultValue('no-reply@example.com')->end()
                        ->scalarNode('sender_name')->defaultNull()->end()
                        ->scalarNode('template')->defaultValue('@SchebTwoFactor/Authentication/form.html.twig')->end()
                        ->scalarNode('digits')->defaultValue(4)->end()
                    ->end()
                ->end()
                ->arrayNode('google')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('issuer')->defaultNull()->end()
                        ->scalarNode('server_name')->defaultNull()->end()
                        ->scalarNode('template')->defaultValue('@SchebTwoFactor/Authentication/form.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('security_tokens')
                    ->defaultValue(array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken"))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ip_whitelist')
                    ->defaultValue(array())
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
