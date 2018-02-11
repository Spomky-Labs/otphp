<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Scheb\TwoFactorBundle\Security\Authentication\Provider\TwoFactorAuthenticationProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TwoFactorProviderHandler;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPassTest extends TestCase
{
    /**
     * @var ProviderCompilerPass
     */
    private $compilerPass;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $twoFactorHandlerDefinition;

    /**
     * @var Definition
     */
    private $firewallAuthenticationProviderDefinition;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new ProviderCompilerPass();
    }

    private function stubTaggedContainerService(array $taggedServices)
    {
        $this->createServiceDefinitions();
        $this->container->setDefinition('scheb_two_factor.provider_handler', $this->twoFactorHandlerDefinition);
        $this->container->setDefinition('scheb_two_factor.security.authentication.provider', $this->firewallAuthenticationProviderDefinition);

        foreach ($taggedServices as $id => $tags) {
            $definition = $this->container->register($id);

            foreach ($tags as $attributes) {
                $definition->addTag('scheb_two_factor.provider', $attributes);
            }
        }
    }

    private function createServiceDefinitions()
    {
        $this->twoFactorHandlerDefinition = new Definition(TwoFactorProviderHandler::class);
        $this->twoFactorHandlerDefinition->setArguments([null]);

        $this->firewallAuthenticationProviderDefinition = new Definition(TwoFactorAuthenticationProvider::class);
        $this->firewallAuthenticationProviderDefinition->setArguments([
            null,
            'firewallName',
            new Reference('event_dispatcher')
        ]);
    }

    private function assertTwoFactorHandlerArgument(array $providers)
    {
        $providersArgument = $this->container->getDefinition('scheb_two_factor.provider_handler')->getArgument(0);
        $this->assertInstanceOf(IteratorArgument::class, $providersArgument);
        $this->assertCount(count($providers), $providersArgument->getValues());
    }

    private function assertFirewallAuthenticationProviderArgument(array $providers)
    {
        $providersArgument = $this->container->getDefinition('scheb_two_factor.security.authentication.provider')->getArgument(0);
        $this->assertInstanceOf(IteratorArgument::class, $providersArgument);
        $this->assertCount(count($providers), $providersArgument->getValues());
    }

    /**
     * @test
     */
    public function process_notHasDefinition_doNothing()
    {
        $this->compilerPass->process($this->container);

        $this->assertFalse($this->container->has('scheb_two_factor.provider_handler'));
    }

    /**
     * @test
     */
    public function process_noTaggedServices_replaceArgumentWithEmptyArray()
    {
        $this->createServiceDefinitions();
        $taggedServices = [];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $this->assertTwoFactorHandlerArgument([]);
        $this->assertFirewallAuthenticationProviderArgument([]);
    }

    /**
     * @test
     */
    public function process_taggedServices_replaceArgumentWithServiceList()
    {
        $this->createServiceDefinitions();
        $taggedServices = ['serviceId' => [
            0 => ['alias' => 'providerAlias'],
        ]];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $expectedResult = ['providerAlias' => new Reference('serviceId')];
        $this->assertTwoFactorHandlerArgument($expectedResult);
        $this->assertFirewallAuthenticationProviderArgument($expectedResult);
    }

    /**
     * @test
     */
    public function process_missingAlias_throwException()
    {
        $this->createServiceDefinitions();
        $taggedServices = ['serviceId' => [
            0 => [],
        ]];
        $this->stubTaggedContainerService($taggedServices);

        $this->expectException(InvalidArgumentException::class);
        $this->compilerPass->process($this->container);
    }
}
