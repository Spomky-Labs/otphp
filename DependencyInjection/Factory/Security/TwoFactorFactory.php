<?php
namespace Scheb\TwoFactorBundle\DependencyInjection\Factory\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TwoFactorFactory implements SecurityFactoryInterface
{
    public const AUTHENTICATION_PROVIDER_KEY = 'two_factor';

    public function addConfiguration(NodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $node */
        $builder = $node->children();
        $builder
            ->scalarNode('check_path')->defaultValue('/2fa_check')->end()
            ->scalarNode('auth_form_path')->defaultValue('/2fa')->end()
            ->booleanNode('always_use_default_target_path')->defaultValue(false)->end()
            ->scalarNode('default_target_path')->defaultValue('/')->end()
            ->scalarNode('auth_code_parameter_name')->defaultValue('_auth_code')->end()
            ->scalarNode('trusted_parameter_name')->defaultValue('_trusted')->end();
    }

    public function create(ContainerBuilder $container, $firewallName, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthenticationProvider($container, $firewallName, $config);
        $listenerId = $this->createAuthenticationListener($container, $firewallName, $config);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    private function createAuthenticationProvider(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $providerId = 'security.authentication.provider.two_factor.' . $firewallName;
        $container
            ->setDefinition($providerId, new ChildDefinition('scheb_two_factor.security.authentication.provider'))
            ->replaceArgument(2, $firewallName)
            ->replaceArgument(3, $config);

        return $providerId;
    }

    private function createAuthenticationListener(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $successHandlerId = $this->createSuccessHandler($container, $firewallName, $config);
        $failureHandlerId = $this->createFailureHandler($container, $firewallName, $config);

        $listenerId = 'security.authentication.listener.two_factor.' . $firewallName;
        $container
            ->setDefinition($listenerId, new ChildDefinition('scheb_two_factor.security.authentication.listener'))
            ->replaceArgument(3, $firewallName)
            ->replaceArgument(4, new Reference($successHandlerId))
            ->replaceArgument(5, new Reference($failureHandlerId))
            ->replaceArgument(6, $config);
        ;

        return $listenerId;
    }

    private function createSuccessHandler(ContainerBuilder $container, string $firewallName, array $config): string {
        $successHandlerId = 'security.authentication.success_handler.two_factor.' . $firewallName;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition('scheb_two_factor.security.authentication.success_handler'))
            ->replaceArgument(1, $firewallName)
            ->replaceArgument(2, $config);

        return $successHandlerId;
    }

    private function createFailureHandler(ContainerBuilder $container, string $firewallName, array $config): string {
        $successHandlerId = 'security.authentication.failure_handler.two_factor.' . $firewallName;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition('scheb_two_factor.security.authentication.failure_handler'))
            ->replaceArgument(1, $config);

        return $successHandlerId;
    }

    public function getPosition()
    {
        return 'remember_me';
    }

    public function getKey()
    {
        return self::AUTHENTICATION_PROVIDER_KEY;
    }
}
