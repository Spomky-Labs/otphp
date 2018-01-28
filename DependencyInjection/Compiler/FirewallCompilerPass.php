<?php

namespace Scheb\TwoFactorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class FirewallCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $authManagerDefinition = $container->getDefinition('security.authentication.manager');
        $authProviders = $authManagerDefinition->getArgument(0)->getValues();
        $firewallServiceIds = $this->getServicesToDecorate($authProviders);

        foreach ($firewallServiceIds as $firewall => $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $decoratedServiceId = $serviceId . '.two_factor_decorator';
                $container
                    ->setDefinition($decoratedServiceId, new ChildDefinition('scheb_two_factor.security.authentication.provider.decorator'))
                    ->setDecoratedService($serviceId)
                    ->replaceArgument(0, new Reference($decoratedServiceId . '.inner'))
                    ->replaceArgument(1, $firewall);
            }
        }
    }

    private function getServicesToDecorate(array $authProviders)
    {
        $firewallServicesToDecorate = array();
        $authProvidersPerFirewall = $this->getAuthProvidersPerFirewall($authProviders);
        foreach ($authProvidersPerFirewall as $firewall => $authProviders) {
            if (isset($authProviders['two_factor'])) {
                unset($authProviders['two_factor'], $authProviders['anonymous']); // No need to decorate those authentication providers
                if (!isset($firewallServicesToDecorate[$firewall])) {
                    $firewallServicesToDecorate[$firewall] = array();
                }
                $firewallServicesToDecorate[$firewall] = array_merge($firewallServicesToDecorate[$firewall], array_values($authProviders));
            }
        }

        return $firewallServicesToDecorate;
    }

    private function getAuthProvidersPerFirewall(array $authProviders)
    {
        $firewallProviders = array();

        /** @var Reference $authProvider */
        foreach ($authProviders as $authProvider) {
            $authProviderId = (string) $authProvider;
            if (!preg_match('#^security\.authentication\.provider\.([^.]+)\.([^.]+)$#', $authProviderId, $match)) {
                throw new ServiceNotFoundException('Authentication provider id must be security.authentication.provider.*.*, given ' . $authProviderId);
            }
            $provider = $match[1];
            $firewall = $match[2];
            $firewallProviders[$firewall][$provider] = $authProviderId;
        }

        return $firewallProviders;
    }
}
