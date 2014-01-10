<?php
namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SchebTwoFactorExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter("scheb_two_factor.email.sender_email", $config['email']['sender_email']);
        $container->setParameter("scheb_two_factor.email.template", $config['email']['template']);
        $container->setParameter("scheb_two_factor.google.server_name", $config['google']['server_name']);
        $container->setParameter("scheb_two_factor.google.template", $config['google']['template']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        // Load two-factor modules
        if ($config['email']['enabled'] === true) {
            $loader->load('security_email.xml');
        }
        if ($config['google']['enabled'] === true) {
            $loader->load('security_google.xml');
        }
    }
}
