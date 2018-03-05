<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\TwoFactorFirewallConfigCompilerPass;
use Scheb\TwoFactorBundle\DependencyInjection\Compiler\TwoFactorProviderCompilerPass;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class TwoFactorFirewallConfigCompilerPassTest extends TestCase
{
    /**
     * @var TwoFactorProviderCompilerPass
     */
    private $compilerPass;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $firewallContextDefinition;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new TwoFactorFirewallConfigCompilerPass();

        $this->firewallContextDefinition = new Definition(TwoFactorFirewallContext::class);
        $this->firewallContextDefinition->setArguments([null]);
        $this->container->setDefinition('scheb_two_factor.firewall_context', $this->firewallContextDefinition);
    }

    private function stubTaggedContainerService(array $taggedServices)
    {
        foreach ($taggedServices as $id => $tags) {
            $definition = $this->container->register($id);

            foreach ($tags as $attributes) {
                $definition->addTag('scheb_two_factor.firewall_config', $attributes);
            }
        }
    }

    private function assertTwoFactorFirewallContextArgument(array $extepectedConfigs)
    {
        $configsArgument = $this->container->getDefinition('scheb_two_factor.firewall_context')->getArgument(0);
        $this->assertEquals($extepectedConfigs, $configsArgument);
    }

    /**
     * @test
     */
    public function process_noTaggedServices_replaceArgumentWithEmptyArray()
    {
        $taggedServices = [];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $this->assertTwoFactorFirewallContextArgument([]);
    }

    /**
     * @test
     */
    public function process_taggedServices_replaceArgumentWithServiceList()
    {
        $taggedServices = ['serviceId' => [
            0 => ['firewall' => 'firewallName'],
        ]];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $expectedResult = ['firewallName' => new Reference('serviceId')];
        $this->assertTwoFactorFirewallContextArgument($expectedResult);
    }

    /**
     * @test
     */
    public function process_missingAlias_throwException()
    {
        $taggedServices = ['serviceId' => [
            0 => [],
        ]];
        $this->stubTaggedContainerService($taggedServices);

        $this->expectException(InvalidArgumentException::class);
        $this->compilerPass->process($this->container);
    }
}
