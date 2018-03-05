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
    public const DEFAULT_MULTI_FACTOR = false;

    public const PROVIDER_ID_PREFIX = 'security.authentication.provider.two_factor.';
    public const LISTENER_ID_PREFIX = 'security.authentication.listener.two_factor.';
    public const SUCCESS_HANDLER_ID_PREFIX = 'security.authentication.success_handler.two_factor.';
    public const FAILURE_HANDLER_ID_PREFIX = 'security.authentication.failure_handler.two_factor.';
    public const FIREWALL_CONFIG_ID_PREFIX = 'security.firewall_config.two_factor.';

    public const PROVIDER_DEFINITION_ID = 'scheb_two_factor.security.authentication.provider';
    public const LISTENER_DEFINITION_ID = 'scheb_two_factor.security.authentication.listener';
    public const SUCCESS_HANDLER_DEFINITION_ID = 'scheb_two_factor.security.authentication.success_handler';
    public const FAILURE_HANDLER_DEFINITION_ID = 'scheb_two_factor.security.authentication.failure_handler';
    public const FIREWALL_CONFIG_DEFINITION_ID = 'scheb_two_factor.security.firewall_config';

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
            ->scalarNode('trusted_parameter_name')->defaultValue(self::DEFAULT_TRUSTED_PARAMETER_NAME)->end()
            ->booleanNode('multi_factor')->defaultValue(self::DEFAULT_MULTI_FACTOR)->end()
        ;
    }

    public function create(ContainerBuilder $container, $firewallName, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthenticationProvider($container, $firewallName, $config);
        $listenerId = $this->createAuthenticationListener($container, $firewallName, $config);
        $this->createTwoFactorFirewallConfig($container, $firewallName, $config);

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    private function createAuthenticationProvider(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $providerId = self::PROVIDER_ID_PREFIX.$firewallName;
        $container
            ->setDefinition($providerId, new ChildDefinition(self::PROVIDER_DEFINITION_ID))
            ->replaceArgument(0, $firewallName)
            ->replaceArgument(1, $config);

        return $providerId;
    }

    private function createAuthenticationListener(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $successHandlerId = $this->createSuccessHandler($container, $firewallName, $config);
        $failureHandlerId = $this->createFailureHandler($container, $firewallName, $config);

        $listenerId = self::LISTENER_ID_PREFIX.$firewallName;
        $container
            ->setDefinition($listenerId, new ChildDefinition(self::LISTENER_DEFINITION_ID))
            ->replaceArgument(3, $firewallName)
            ->replaceArgument(4, new Reference($successHandlerId))
            ->replaceArgument(5, new Reference($failureHandlerId))
            ->replaceArgument(6, $config);

        return $listenerId;
    }

    private function createSuccessHandler(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $successHandlerId = self::SUCCESS_HANDLER_ID_PREFIX.$firewallName;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition(self::SUCCESS_HANDLER_DEFINITION_ID))
            ->replaceArgument(1, $firewallName)
            ->replaceArgument(2, $config);

        return $successHandlerId;
    }

    private function createFailureHandler(ContainerBuilder $container, string $firewallName, array $config): string
    {
        $successHandlerId = self::FAILURE_HANDLER_ID_PREFIX.$firewallName;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition(self::FAILURE_HANDLER_DEFINITION_ID))
            ->replaceArgument(1, $config);

        return $successHandlerId;
    }

    private function createTwoFactorFirewallConfig(ContainerBuilder $container, string $firewallName, array $config): void
    {
        $firewallConfigId = self::FIREWALL_CONFIG_ID_PREFIX.$firewallName;
        $container
            ->setDefinition($firewallConfigId, new ChildDefinition(self::FIREWALL_CONFIG_DEFINITION_ID))
            ->replaceArgument(0, $config)
            ->addTag('scheb_two_factor.firewall_config', ['firewall' => $firewallName]);

        // The SecurityFactory doesn't have access to the service definitions of the bundle. Therefore we tag the
        // definition so we can find it in a compiler pass and add to the the TwoFactorFirewallContext service.
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return self::AUTHENTICATION_PROVIDER_KEY;
    }
}
