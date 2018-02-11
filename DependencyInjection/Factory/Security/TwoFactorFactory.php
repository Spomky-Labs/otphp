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
    public function addConfiguration(NodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $node */
        $builder = $node->children();
        $builder
            ->scalarNode('check_path')->defaultValue('/2fa_check')->end()
            ->scalarNode('auth_form_path')->defaultValue('/2fa')->end()
            ->booleanNode('always_use_default_target_path')->defaultValue(false)->end()
            ->scalarNode('default_target_path')->defaultValue('/')->end();
    }

    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthenticationProvider($container, $id);
        $listenerId = $this->createAuthenticationListener($container, $id, $config);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    private function createAuthenticationProvider(ContainerBuilder $container, string $id): string
    {
        $providerId = 'security.authentication.provider.two_factor.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition('scheb_two_factor.security.authentication.provider'))
            ->replaceArgument(1, $id);

        return $providerId;
    }

    private function createAuthenticationListener(ContainerBuilder $container, string $id, array $config): string
    {
        $successHandlerId = $this->createSuccessHandler($container, $id, $config);
        $failureHandlerId = $this->createFailureHandler($container, $id, $config);

        $listenerId = 'security.authentication.listener.two_factor.' . $id;
        $container
            ->setDefinition($listenerId, new ChildDefinition('scheb_two_factor.security.authentication.listener'))
            ->replaceArgument(3, $id)
            ->replaceArgument(4, new Reference($successHandlerId))
            ->replaceArgument(5, new Reference($failureHandlerId))
            ->replaceArgument(6, $config);
        ;

        return $listenerId;
    }

    private function createSuccessHandler(ContainerBuilder $container, string $id, array $config): string {
        $successHandlerId = 'security.authentication.success_handler.two_factor.' . $id;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition('scheb_two_factor.security.authentication.success_handler'))
            ->replaceArgument(1, $id)
            ->replaceArgument(2, $config);

        return $successHandlerId;
    }

    private function createFailureHandler(ContainerBuilder $container, string $id, array $config): string {
        $successHandlerId = 'security.authentication.failure_handler.two_factor.' . $id;
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
        return 'two-factor';
    }
}
