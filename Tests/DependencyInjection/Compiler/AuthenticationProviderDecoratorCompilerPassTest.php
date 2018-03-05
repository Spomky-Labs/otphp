<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection\Compiler;

use Scheb\TwoFactorBundle\DependencyInjection\Compiler\AuthenticationProviderDecoratorCompilerPass;
use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

class AuthenticationProviderDecoratorCompilerPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var AuthenticationProviderDecoratorCompilerPass
     */
    private $compilerPass;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->compilerPass = new AuthenticationProviderDecoratorCompilerPass();
    }

    private function stubAuthenticationManagerWithProviders(array $providerIds): void
    {
        $providerReferences = [];
        foreach ($providerIds as $providerId) {
            $providerDefinition = new Definition(AuthenticationProviderInterface::class);
            $this->container->setDefinition($providerId, $providerDefinition);
            $providerReferences[] = new Reference($providerId);
        }
        $authenticationManagerDefinition = new Definition(AuthenticationManagerInterface::class);
        $authenticationManagerDefinition->setArgument(0, new IteratorArgument($providerReferences));
        $this->container->setDefinition('security.authentication.manager', $authenticationManagerDefinition);
    }

    private function assertContainerHasDecoratedProvider(string $providerId): void
    {
        $expectedDecoratorId = $providerId.'.two_factor_decorator';
        $expectedDecoratedId = $expectedDecoratorId.'.inner';

        $this->assertTrue($this->container->hasDefinition($expectedDecoratorId), 'Must have service "'.$expectedDecoratorId.'" defined.');

        $decoratorDefinition = $this->container->getDefinition($expectedDecoratorId);
        $decoratedServiceReference = $decoratorDefinition->getArgument(0);
        $this->assertEquals($expectedDecoratedId, (string) $decoratedServiceReference);
        $this->assertEquals($providerId, $decoratorDefinition->getDecoratedService()[0]);
    }

    private function assertContainerNotHasDecoratedProvider(string $providerId): void
    {
        $expectedDecoratorId = $providerId.'.two_factor_decorator';
        $this->assertFalse($this->container->hasDefinition($expectedDecoratorId), 'Must not have service "'.$expectedDecoratorId.'" defined.');
    }

    /**
     * @test
     */
    public function process_hasMultipleAuthenticationProviders_decorateAll()
    {
        $this->stubAuthenticationManagerWithProviders([
            'security.provider.foo',
            'security.provider.bar',
            TwoFactorFactory::PROVIDER_ID_PREFIX.'.main', // This is the two-factor provider, must not be decorated
        ]);

        $this->compilerPass->process($this->container);

        $this->assertContainerHasDecoratedProvider('security.provider.foo');
        $this->assertContainerHasDecoratedProvider('security.provider.bar');
        $this->assertContainerNotHasDecoratedProvider('security.authentication.provider.two_factor.main');
    }
}
