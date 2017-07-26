<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Scheb\TwoFactorBundle\Tests\TestCase;

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

    /**
     * @var Definition
     */
    private $voterDefinition;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new ProviderCompilerPass();
    }

    private function stubContainerService($taggedServices)
    {
        $this->createServiceDefinition();
        $this->container->setDefinition('scheb_two_factor.provider_registry', $this->registryDefinition);
        $this->container->setDefinition('scheb_two_factor.security_voter', $this->voterDefinition);

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

        $this->voterDefinition = new Definition('Scheb\TwoFactorBundle\Security\TwoFactor\Voter');
        $this->voterDefinition->setArguments(array(
            new Reference('scheb_two_factor.session_flag_manager'),
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
        $this->assertFalse($this->container->has('scheb_two_factor.security_voter'));
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
        $this->assertSame(array(), $this->container->getDefinition('scheb_two_factor.security_voter')->getArgument(1));
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
        $this->assertSame(array('providerAlias'), $this->container->getDefinition('scheb_two_factor.security_voter')->getArgument(1));
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
