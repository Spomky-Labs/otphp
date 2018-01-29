<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
    private $registryDefinition;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new ProviderCompilerPass();
    }

    private function stubContainerService($taggedServices)
    {
        $this->createServiceDefinition();
        $this->container->setDefinition('scheb_two_factor.provider_registry', $this->registryDefinition);

        foreach ($taggedServices as $id => $tags) {
            $definition = $this->container->register($id);

            foreach ($tags as $attributes) {
                $definition->addTag('scheb_two_factor.provider', $attributes);
            }
        }
    }

    private function createServiceDefinition()
    {
        $this->registryDefinition = new Definition('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry');
        $this->registryDefinition->setArguments(array(
            new Reference('scheb_two_factor.session_flag_manager'),
            new Reference('event_dispatcher'),
            '%scheb_two_factor.parameter_names.auth_code%',
            null,
        ));
    }

    /**
     * @test
     */
    public function process_notHasDefinition_doNothing()
    {
        $this->compilerPass->process($this->container);

        $this->assertFalse($this->container->has('scheb_two_factor.provider_registry'));
    }

    /**
     * @test
     */
    public function process_noTaggedServices_replaceArgumentWithEmptyArray()
    {
        $this->createServiceDefinition();
        $taggedServices = array();
        $this->stubContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $this->assertSame(array(), $this->container->getDefinition('scheb_two_factor.provider_registry')->getArgument(3));
    }

    /**
     * @test
     */
    public function process_taggedServices_replaceArgumentWithServiceList()
    {
        $this->createServiceDefinition();
        $taggedServices = array('serviceId' => array(
            0 => array('alias' => 'providerAlias'),
        ));
        $this->stubContainerService($taggedServices);

        $this->compilerPass->process($this->container);

        $this->assertEquals(array('providerAlias' => new Reference('serviceId')), $this->container->getDefinition('scheb_two_factor.provider_registry')->getArgument(3));
    }

    /**
     * @test
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process_missingAlias_throwException()
    {
        $this->createServiceDefinition();
        $taggedServices = array('serviceId' => array(
            0 => array(),
        ));
        $this->stubContainerService($taggedServices);

        $this->compilerPass->process($this->container);
    }
}
