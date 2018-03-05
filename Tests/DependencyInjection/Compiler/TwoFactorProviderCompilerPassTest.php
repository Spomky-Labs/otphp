<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\TwoFactorProviderCompilerPass;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class TwoFactorProviderCompilerPassTest extends TestCase
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
    private $providerRegistryDefinition;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new TwoFactorProviderCompilerPass();

        $this->providerRegistryDefinition = new Definition(TwoFactorProviderRegistry::class);
        $this->providerRegistryDefinition->setArguments([null]);
        $this->container->setDefinition('scheb_two_factor.provider_registry', $this->providerRegistryDefinition);
    }

    private function stubTaggedContainerService(array $taggedServices)
    {
        foreach ($taggedServices as $id => $tags) {
            $definition = $this->container->register($id);

            foreach ($tags as $attributes) {
                $definition->addTag('scheb_two_factor.provider', $attributes);
            }
        }
    }

    private function assertProviderRegistryArgument(array $providers)
    {
        $providersArgument = $this->container->getDefinition('scheb_two_factor.provider_registry')->getArgument(0);
        $this->assertInstanceOf(IteratorArgument::class, $providersArgument);
        $this->assertCount(count($providers), $providersArgument->getValues());
    }

    /**
     * @test
     */
    public function process_noTaggedServices_replaceArgumentWithEmptyArray()
    {
        $taggedServices = [];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $this->assertProviderRegistryArgument([]);
    }

    /**
     * @test
     */
    public function process_taggedServices_replaceArgumentWithServiceList()
    {
        $taggedServices = ['serviceId' => [
            0 => ['alias' => 'providerAlias'],
        ]];
        $this->stubTaggedContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $expectedResult = ['providerAlias' => new Reference('serviceId')];
        $this->assertProviderRegistryArgument($expectedResult);
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
