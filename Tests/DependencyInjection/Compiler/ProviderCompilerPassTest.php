<?php
namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProviderCompilerPassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Scheb\TwoFactorBundle\DependencyInjection\Compiler\ProviderCompilerPass
     */
    private $compilerPass;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $definition;

    public function setUp()
    {
        $this->container = $this->getMockBuilder("Symfony\Component\DependencyInjection\ContainerBuilder")
            ->disableOriginalConstructor()
            ->getMock();

        $this->compilerPass = new ProviderCompilerPass();
    }

    private function stubContainerService($taggedServices)
    {
        $this->createServiceDefinition();
        $this->container
            ->expects($this->once())
            ->method("hasDefinition")
            ->with("scheb_two_factor.provider_collection")
            ->will($this->returnValue(true));
        $this->container
            ->expects($this->once())
            ->method("getDefinition")
            ->with("scheb_two_factor.provider_collection")
            ->will($this->returnValue($this->definition));
        $this->container
            ->expects($this->once())
            ->method("findTaggedServiceIds")
            ->with("scheb_two_factor.provider")
            ->will($this->returnValue($taggedServices));
    }

    private function createServiceDefinition()
    {
        $this->definition = $this->getMockBuilder("Symfony\Component\DependencyInjection\Definition")
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function process_notHasDefinition_doNothing()
    {
        //Expect get never be called
        $this->container
            ->expects($this->once())
            ->method("hasDefinition")
            ->with("scheb_two_factor.provider_collection")
            ->will($this->returnValue(false));
        $this->container
            ->expects($this->never())
            ->method("getDefinition");

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function process_noTaggedServices_noProviderAddedToCollection()
    {
        $this->createServiceDefinition();
        $taggedServices = array();
        $this->stubContainerService($taggedServices);

        //Mock the Definition
        $this->definition
            ->expects($this->never())
            ->method("addMethodCall");

        $this->compilerPass->process($this->container);
    }

    /**
     * @test
     */
    public function process_taggedServices_addProviderToCollection()
    {
        $this->createServiceDefinition();
        $taggedServices = array('serviceId' => array(
            0 => array('alias' => 'providerAlias')
        ));
        $this->stubContainerService($taggedServices);

        //Mock the Definition
        $this->definition
            ->expects($this->once())
            ->method("addMethodCall")
            ->with('addProvider', array('providerAlias', new Reference("serviceId")));

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
            0 => array()
        ));
        $this->stubContainerService($taggedServices);

        $this->compilerPass->process($this->container);
    }
}
