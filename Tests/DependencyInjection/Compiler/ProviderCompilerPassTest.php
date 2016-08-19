<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\Reference;
use Scheb\TwoFactorBundle\Tests\TestCase;

class ProviderCompilerPassTest extends TestCase
{
    /**
     * @var ProviderCompilerPass
     */
    private $compilerPass;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registryDefinition;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $voterDefinition;

    public function setUp()
    {
        $this->container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->compilerPass = new ProviderCompilerPass();
    }

    private function stubContainerService($taggedServices)
    {
        $this->createServiceDefinition();
        $this->container
            ->expects($this->at(0))
            ->method('hasDefinition')
            ->with('scheb_two_factor.provider_registry')
            ->willReturn(true);
        $this->container
            ->expects($this->at(1))
            ->method('getDefinition')
            ->with('scheb_two_factor.provider_registry')
            ->willReturn($this->registryDefinition);
        $this->container
            ->expects($this->at(2))
            ->method('getDefinition')
            ->with('scheb_two_factor.security_voter')
            ->willReturn($this->voterDefinition);
        $this->container
            ->expects($this->at(3))
            ->method('findTaggedServiceIds')
            ->with('scheb_two_factor.provider')
            ->willReturn($taggedServices);
    }

    private function createServiceDefinition()
    {
        $this->registryDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');
        $this->voterDefinition = $this->createMock('Symfony\Component\DependencyInjection\Definition');
    }

    /**
     * @test
     */
    public function process_notHasDefinition_doNothing()
    {
        //Expect get never be called
        $this->container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('scheb_two_factor.provider_registry')
            ->willReturn(false);
        $this->container
            ->expects($this->never())
            ->method('getDefinition');

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function process_noTaggedServices_replaceArgumentWithEmptyArray()
    {
        $this->createServiceDefinition();
        $taggedServices = array();
        $this->stubContainerService($taggedServices);

        //Mock the Definition
        $this->registryDefinition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(3, array());
        $this->voterDefinition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(1, array());

        $this->compilerPass->process($this->container);
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

        //Mock the Definition
        $this->registryDefinition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(3, array('providerAlias' => new Reference('serviceId')));
        $this->voterDefinition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with(1, array('providerAlias'));

        $this->compilerPass->process($this->container);
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
