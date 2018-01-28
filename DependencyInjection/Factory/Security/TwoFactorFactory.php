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
    /**
     * @param NodeDefinition $node
     */
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

    /**
     * Configures the container services required to use the authentication listener.
     *
     * @param ContainerBuilder $container
     * @param string           $id                The unique id of the firewall
     * @param array            $config            The options array for the listener
     * @param string           $userProvider      The service id of the user provider
     * @param string           $defaultEntryPoint
     *
     * @return array containing three values:
     *               - the provider id
     *               - the listener id
     *               - the entry point id
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = $this->createAuthenticationProvider($container, $id);
        $listenerId = $this->createAuthenticationListener($container, $id, $config);

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     *
     * @return string
     */
    private function createAuthenticationProvider(ContainerBuilder $container, $id)
    {
        $providerId = 'security.authentication.provider.two_factor.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition('scheb_two_factor.security.authentication.provider'))
            ->replaceArgument(0, $id);

        return $providerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     *
     * @return string
     */
    private function createAuthenticationListener(ContainerBuilder $container, $id, $config)
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

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     *
     * @return string
     */
    private function createSuccessHandler(ContainerBuilder $container, $id, $config) {
        $successHandlerId = 'security.authentication.success_handler.two_factor.' . $id;
        $container
            ->setDefinition($successHandlerId, new ChildDefinition('scheb_two_factor.security.authentication.success_handler'))
            ->replaceArgument(1, $id)
            ->replaceArgument(2, $config);

        return $successHandlerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     *
     * @return string
     */
    private function createFailureHandler(ContainerBuilder $container, $id, $config) {
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
