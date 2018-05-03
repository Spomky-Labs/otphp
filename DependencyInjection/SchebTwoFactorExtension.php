<?php

namespace Scheb\TwoFactorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SchebTwoFactorExtension extends Extension
{
    const DEFAULT_TRUSTED_DEVICE_MANAGER = 'scheb_two_factor.default_trusted_device_manager';
    const DEFAULT_BACKUP_CODE_MANAGER = 'scheb_two_factor.default_backup_code_manager';

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
        $container->setParameter('scheb_two_factor.trusted_device.enabled', $config['trusted_device']['enabled']);
        $container->setParameter('scheb_two_factor.trusted_device.cookie_name', $config['trusted_device']['cookie_name']);
        $container->setParameter('scheb_two_factor.trusted_device.lifetime', $config['trusted_device']['lifetime']);
        $container->setParameter('scheb_two_factor.trusted_device.extend_lifetime', $config['trusted_device']['extend_lifetime']);
        $container->setParameter('scheb_two_factor.trusted_device.cookie_secure', $config['trusted_device']['cookie_secure']);
        $container->setParameter('scheb_two_factor.trusted_device.cookie_same_site', $config['trusted_device']['cookie_same_site']);
        $container->setParameter('scheb_two_factor.security_tokens', $config['security_tokens']);
        $container->setParameter('scheb_two_factor.ip_whitelist', $config['ip_whitelist']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.xml');
        $loader->load('persistence.xml');
        $loader->load('trusted_device.xml');
        $loader->load('backup_codes.xml');
        $loader->load('two_factor.xml');

        // Load two-factor modules
        if ($config['email']['enabled'] === true) {
            $this->configureEmailAuthenticationProvider($container, $config);
        }
        if ($config['google']['enabled'] === true) {
            $this->configureGoogleAuthenticationProvider($container);
        }

        // Configure custom services
        $this->configurePersister($container, $config);
        if ($config['trusted_device']['enabled'] === true) {
            $this->configureTrustedDeviceManager($container, $config);
        }
        if ($config['backup_codes']['enabled'] === true) {
            $this->configureBackupCodeManager($container, $config);
        }
    }

    private function configurePersister(ContainerBuilder $container, array $config): void
    {
        // No custom persister configured
        if (!$config['persister']) {
            return;
        }

        $container->removeAlias($container->getAlias('scheb_two_factor.persister'));
        $container->setAlias('scheb_two_factor.persister', $config['persister']);
    }

    private function configureTrustedDeviceManager(ContainerBuilder $container, array $config): void
    {
        $trustedDeviceManager = $config['trusted_device']['manager'] ?? self::DEFAULT_TRUSTED_DEVICE_MANAGER;
        $container->removeAlias($container->getAlias('scheb_two_factor.trusted_device_manager'));
        $container->setAlias('scheb_two_factor.trusted_device_manager', $trustedDeviceManager);
    }

    private function configureBackupCodeManager(ContainerBuilder $container, array $config): void
    {
        $backupCodeManager = $config['backup_codes']['manager'] ?? self::DEFAULT_BACKUP_CODE_MANAGER;
        $container->removeAlias($container->getAlias('scheb_two_factor.backup_code_manager'));
        $container->setAlias('scheb_two_factor.backup_code_manager', $backupCodeManager);
    }

    private function configureEmailAuthenticationProvider(ContainerBuilder $container, array $config): void
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('two_factor_provider_email.xml');

        $mailerService = $config['email']['mailer'];
        if ($mailerService) {
            $container->removeAlias('scheb_two_factor.security.email.auth_code_mailer');
            $container->setAlias('scheb_two_factor.security.email.auth_code_mailer', $mailerService);
        }

        $codeGeneratorService = $config['email']['code_generator'];
        if ($codeGeneratorService) {
            $container->removeAlias('scheb_two_factor.security.email.code_generator');
            $container->setAlias('scheb_two_factor.security.email.code_generator', $codeGeneratorService);
        }
    }

    private function configureGoogleAuthenticationProvider(ContainerBuilder $container): void
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('two_factor_provider_google.xml');
    }
}
