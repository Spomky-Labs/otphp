<?php

namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class SchebTwoFactorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('scheb_two_factor.model_manager_name', $config['model_manager_name']);
        $container->setParameter('scheb_two_factor.email.sender_email', $config['email']['sender_email']);
        $container->setParameter('scheb_two_factor.email.sender_name', $config['email']['sender_name']);
        $container->setParameter('scheb_two_factor.email.template', $config['email']['template']);
        $container->setParameter('scheb_two_factor.email.digits', $config['email']['digits']);
        $container->setParameter('scheb_two_factor.google.server_name', $config['google']['server_name']);
        $container->setParameter('scheb_two_factor.google.issuer', $config['google']['issuer']);
        $container->setParameter('scheb_two_factor.google.template', $config['google']['template']);
        $container->setParameter('scheb_two_factor.trusted_computer.enabled', $config['trusted_computer']['enabled']);
        $container->setParameter('scheb_two_factor.trusted_computer.cookie_name', $config['trusted_computer']['cookie_name']);
        $container->setParameter('scheb_two_factor.trusted_computer.cookie_lifetime', $config['trusted_computer']['cookie_lifetime']);
        $container->setParameter('scheb_two_factor.trusted_computer.cookie_secure', $config['trusted_computer']['cookie_secure']);
        $container->setParameter('scheb_two_factor.trusted_computer.cookie_same_site', $config['trusted_computer']['cookie_same_site']);
        $container->setParameter('scheb_two_factor.security_tokens', $config['security_tokens']);
        $container->setParameter('scheb_two_factor.ip_whitelist', $config['ip_whitelist']);
        $container->setParameter('scheb_two_factor.parameter_names.auth_code', $config['parameter_names']['auth_code']);
        $container->setParameter('scheb_two_factor.parameter_names.trusted', $config['parameter_names']['trusted']);
        $container->setParameter('scheb_two_factor.exclude_pattern', $config['exclude_pattern']);

        // Load two-factor modules
        if ($config['email']['enabled'] === true) {
            $this->configureEmail($container, $config);
        }
        if ($config['google']['enabled'] === true) {
            $this->configureGoogle($container);
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.xml');
        $loader->load('persistence.xml');

        // Configure persister service
        $this->configurePersister($container, $config);
    }

    /**
     * Configure the persister service.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws InvalidArgumentException
     */
    private function configurePersister(ContainerBuilder $container, $config)
    {
        // No custom persister configured
        if (!$config['persister']) {
            return;
        }

        // Replace arguments
        $persisterId = $config['persister'];
        $persisterArguments = array(
            'scheb_two_factor.trusted_computer_manager' => 0,
            'scheb_two_factor.security.email.code_generator' => 0,
            'scheb_two_factor.backup_code_validator' => 0,
        );
        foreach ($persisterArguments as $id => $index) {
            if ($container->hasDefinition($id)) {
                $definition = $container->getDefinition($id);
                $definition->replaceArgument($index, new Reference($persisterId));
            }
        }
    }

    /**
     * Configure email two-factor authentication.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureEmail(ContainerBuilder $container, $config)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security_email.xml');
        $mailerService = $config['email']['mailer'];
        if ($mailerService) {
            $definition = $container->getDefinition('scheb_two_factor.security.email.code_generator');
            $definition->replaceArgument(1, new Reference($mailerService));
        }
    }

    /**
     * Configure Google Authenticator two-factor authentication.
     *
     * @param ContainerBuilder $container
     */
    private function configureGoogle(ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security_google.xml');
    }
}
