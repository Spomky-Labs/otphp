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
    public const DEFAULT_CHECK_PATH = '/2fa_check';
    public const DEFAULT_AUTH_FORM_PATH = '/2fa';
    public const DEFAULT_ALWAYS_USE_DEFAULT_TARGET_PATH = false;
    public const DEFAULT_TARGET_PATH = '/';
    public const DEFAULT_AUTH_CODE_PARAMETER_NAME = '_auth_code';
    public const DEFAULT_TRUSTED_PARAMETER_NAME = '_trusted';

    public function addConfiguration(NodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $node */
        $builder = $node->children();
        $builder
            ->scalarNode('check_path')->defaultValue(self::DEFAULT_CHECK_PATH)->end()
            ->scalarNode('auth_form_path')->defaultValue(self::DEFAULT_AUTH_FORM_PATH)->end()
            ->booleanNode('always_use_default_target_path')->defaultValue(self::DEFAULT_ALWAYS_USE_DEFAULT_TARGET_PATH)->end()
            ->scalarNode('default_target_path')->defaultValue(self::DEFAULT_TARGET_PATH)->end()
            ->scalarNode('auth_code_parameter_name')->defaultValue(self::DEFAULT_AUTH_CODE_PARAMETER_NAME)->end()
            ->scalarNode('trusted_parameter_name')->defaultValue(self::DEFAULT_TRUSTED_PARAMETER_NAME)->end();
    }

    public function create(ContainerBuilder $container, $firewallName, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthenticationProvider($container, $firewallName);
        $listenerId = $this->createAuthenticationListener($container, $firewallName, $config);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    private function createAuthenticationProvider(ContainerBuilder $container, string $firewallName): string
    {
        $providerId = 'security.authentication.provider.two_factor.' . $firewallName;
        $container
            ->setDefinition($providerId, new ChildDefinition('scheb_two_factor.security.authentication.provider'))
            ->replaceArgument(1, $firewallName);

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
