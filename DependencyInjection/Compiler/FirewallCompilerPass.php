<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Ivory\CKEditorBundle\Exception\DependencyInjectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class FirewallCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $authManagerDefinition = $container->getDefinition('security.authentication.manager');
        $authProviders = $authManagerDefinition->getArgument(0);
        $serviceIds = $this->getServicesToDecorate($authProviders);

        foreach ($serviceIds as $serviceId) {
            $decoratedServiceId = $serviceId . '.two_factor_decorator';
            $container
                ->setDefinition($decoratedServiceId, new DefinitionDecorator('scheb_two_factor.security.authentication.provider.decorator'))
                ->setDecoratedService($serviceId)
                ->replaceArgument(0, new Reference($decoratedServiceId . '.inner'));
        }
    }

    private function getServicesToDecorate(array $authProviders)
    {
        $servicesToDecorate = array();
        $authProvidersPerFirewall = $this->getAuthProvidersPerFirewall($authProviders);
        foreach ($authProvidersPerFirewall as $firewall => $authProviders) {
            if (isset($authProviders['two_factor'])) {
                unset($authProviders['two_factor'], $authProviders['anonymous']); // No need to decorate those authentication providers
                $servicesToDecorate = array_merge($servicesToDecorate, array_values($authProviders));
            }
        }

        return $servicesToDecorate;
    }

    private function getAuthProvidersPerFirewall(array $authProviders)
    {
        $firewallProviders = array();

        /** @var Reference $authProvider */
        foreach ($authProviders as $authProvider) {
            $authProviderId = (string) $authProvider;
            if (!preg_match('#^security\.authentication\.provider\.([^.]+)\.([^.]+)$#', $authProviderId, $match)) {
                throw new DependencyInjectionException('Authentication provider id must be security.authentication.provider.*.*, given ' . $authProviderId);
            }
            $provider = $match[1];
            $firewall = $match[2];
            $firewallProviders[$firewall][$provider] = $authProviderId;
        }

        return $firewallProviders;
    }
}
