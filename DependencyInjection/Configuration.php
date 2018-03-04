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
                ->arrayNode('trusted_device')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('manager')->defaultNull()->end()
                        ->scalarNode('lifetime')->defaultValue(60 * 24 * 3600)->end()
                        ->booleanNode('extend_lifetime')->defaultValue(false)->end()
                        ->scalarNode('cookie_name')->defaultValue('trusted_device')->end()
                        ->booleanNode('cookie_secure')->defaultValue(false)->end()
                        ->scalarNode('cookie_same_site')
                            ->defaultValue('lax')
                            ->validate()
                            ->ifNotInArray(['lax', 'strict'])
                                ->thenInvalid('Invalid cookie same-site value %s, must be "lax" or "strict"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('backup_codes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('manager')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('mailer')->defaultNull()->end()
                        ->scalarNode('code_generator')->defaultNull()->end()
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
                    ->defaultValue(["Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken"])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('ip_whitelist')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
