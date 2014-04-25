<?php
namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class SchebTwoFactorExtension extends Extension
{

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter("scheb_two_factor.model_manager_name", $config['model_manager_name']);
        $container->setParameter("scheb_two_factor.email.sender_email", $config['email']['sender_email']);
        $container->setParameter("scheb_two_factor.email.template", $config['email']['template']);
        $container->setParameter("scheb_two_factor.email.digits", $config['email']['digits']);
        $container->setParameter("scheb_two_factor.google.server_name", $config['google']['server_name']);
        $container->setParameter("scheb_two_factor.google.template", $config['google']['template']);
        $container->setParameter("scheb_two_factor.trusted_computer.enabled", $config['trusted_computer']['enabled']);
        $container->setParameter("scheb_two_factor.trusted_computer.cookie_name", $config['trusted_computer']['cookie_name']);
        $container->setParameter("scheb_two_factor.trusted_computer.cookie_lifetime", $config['trusted_computer']['cookie_lifetime']);

        // Load two-factor modules
        if ($config['email']['enabled'] === true) {
            $this->configureEmail($container, $config);
        }
        if ($config['google']['enabled'] === true) {
            $this->configureGoogle($container, $config);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load("security.xml");
        $loader->load("listeners.xml");
        $loader->load("doctrine.xml");
    }

    /**
     * Configure email two-factor authentication
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    public function configureEmail(ContainerBuilder $container, $config)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('security_email.xml');
        $mailerService = $config['email']['mailer'];
        if ($mailerService) {
            if ($container->hasDefinition($mailerService)) {
                throw new InvalidArgumentException('Mailer service "'.$mailerService.'" does not exist!');
            }
            $definition = $container->getDefinition("scheb_two_factor.security.email.code_manager");
            $definition->replaceArgument(1, new Reference($mailerService));
        }
    }

    /**
     * Configure Google Authenticator two-factor authentication
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     */
    public function configureGoogle(ContainerBuilder $container, $config)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('security_google.xml');
    }
}
