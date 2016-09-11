<?php

namespace Scheb\TwoFactorBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Scheb\TwoFactorBundle\DependencyInjection\SchebTwoFactorExtension;
use Symfony\Component\DependencyInjection\Definition;
use Scheb\TwoFactorBundle\Tests\TestCase;

class SchebTwoFactorExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var SchebTwoFactorExtension
     */
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new SchebTwoFactorExtension();

        //Stub services
        $this->container->setDefinition('acme_test.persister', new Definition());
        $this->container->setDefinition('acme_test.mailer', new Definition());
    }

    /**
     * @test
     */
    public function load_emptyConfig_setDefaultValues()
    {
        $config = $this->getEmptyConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertParameter(null, 'scheb_two_factor.model_manager_name');
        $this->assertParameter('_auth_code', 'scheb_two_factor.parameter_names.auth_code');
        $this->assertParameter('_trusted', 'scheb_two_factor.parameter_names.trusted');
        $this->assertParameter(null, 'scheb_two_factor.model_manager_name');
        $this->assertParameter('no-reply@example.com', 'scheb_two_factor.email.sender_email');
        $this->assertParameter(null, 'scheb_two_factor.email.sender_name');
        $this->assertParameter('SchebTwoFactorBundle:Authentication:form.html.twig', 'scheb_two_factor.email.template');
        $this->assertParameter(4, 'scheb_two_factor.email.digits');
        $this->assertParameter(null, 'scheb_two_factor.google.server_name');
        $this->assertParameter(null, 'scheb_two_factor.google.issuer');
        $this->assertParameter('SchebTwoFactorBundle:Authentication:form.html.twig', 'scheb_two_factor.google.template');
        $this->assertParameter(false, 'scheb_two_factor.trusted_computer.enabled');
        $this->assertParameter('trusted_computer', 'scheb_two_factor.trusted_computer.cookie_name');
        $this->assertParameter(5184000, 'scheb_two_factor.trusted_computer.cookie_lifetime');
        $this->assertParameter(false, 'scheb_two_factor.trusted_computer.cookie_secure');
        $this->assertParameter('lax', 'scheb_two_factor.trusted_computer.cookie_same_site');
        $this->assertParameter(array('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'), 'scheb_two_factor.security_tokens');
        $this->assertParameter(array(), 'scheb_two_factor.ip_whitelist');
        $this->assertParameter(null, 'scheb_two_factor.exclude_pattern');
    }

    /**
     * @test
     */
    public function load_fullConfig_setConfigValues()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertParameter('alternative', 'scheb_two_factor.model_manager_name');
        $this->assertParameter('authCodeName', 'scheb_two_factor.parameter_names.auth_code');
        $this->assertParameter('trustedName', 'scheb_two_factor.parameter_names.trusted');
        $this->assertParameter('me@example.com', 'scheb_two_factor.email.sender_email');
        $this->assertParameter('Sender Name', 'scheb_two_factor.email.sender_name');
        $this->assertParameter('AcmeTestBundle:Authentication:emailForm.html.twig', 'scheb_two_factor.email.template');
        $this->assertParameter(6, 'scheb_two_factor.email.digits');
        $this->assertParameter('Server Name', 'scheb_two_factor.google.server_name');
        $this->assertParameter('Issuer', 'scheb_two_factor.google.issuer');
        $this->assertParameter('AcmeTestBundle:Authentication:googleForm.html.twig', 'scheb_two_factor.google.template');
        $this->assertParameter(true, 'scheb_two_factor.trusted_computer.enabled');
        $this->assertParameter('trusted_cookie', 'scheb_two_factor.trusted_computer.cookie_name');
        $this->assertParameter(2592000, 'scheb_two_factor.trusted_computer.cookie_lifetime');
        $this->assertParameter(true, 'scheb_two_factor.trusted_computer.cookie_secure');
        $this->assertParameter('strict', 'scheb_two_factor.trusted_computer.cookie_same_site');
        $this->assertParameter(array('Symfony\Component\Security\Core\Authentication\Token\SomeToken'), 'scheb_two_factor.security_tokens');
        $this->assertParameter(array('127.0.0.1'), 'scheb_two_factor.ip_whitelist');
        $this->assertParameter('excludePattern', 'scheb_two_factor.exclude_pattern');
    }

    /**
     * @test
     */
    public function load_emptyConfig_loadBasicServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        //Security
        $this->assertHasDefinition('scheb_two_factor.session_flag_manager');
        $this->assertHasDefinition('scheb_two_factor.session_flag_generator');
        $this->assertHasDefinition('scheb_two_factor.trusted_cookie_manager');
        $this->assertHasDefinition('scheb_two_factor.trusted_token_generator');
        $this->assertHasDefinition('scheb_two_factor.trusted_filter');
        $this->assertHasDefinition('scheb_two_factor.provider_registry');
        $this->assertHasDefinition('scheb_two_factor.backup_code_validator');

        //Doctrine
        $this->assertHasDefinition('scheb_two_factor.entity_manager');
    }

    /**
     * @test
     */
    public function load_noAuthEnabled_notLoadServices()
    {
        $config = $this->getEmptyConfig();
        $this->extension->load(array($config), $this->container);

        //Google
        $this->assertNotHasDefinition('scheb_two_factor.security.google');
        $this->assertNotHasDefinition('scheb_two_factor.security.google_authenticator');
        $this->assertNotHasDefinition('scheb_two_factor.security.google.provider');

        //Email
        $this->assertNotHasDefinition('scheb_two_factor.auth_code_mailer');
        $this->assertNotHasDefinition('scheb_two_factor.security.email.code_manager');
        $this->assertNotHasDefinition('scheb_two_factor.security.email.provider');
    }

    /**
     * @test
     */
    public function load_googleAuthEnabled_loadGoogleServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertHasDefinition('scheb_two_factor.security.google');
        $this->assertHasDefinition('scheb_two_factor.security.google_authenticator');
        $this->assertHasDefinition('scheb_two_factor.security.google.provider');
        $this->assertHasDefinition('scheb_two_factor.security.google.google_code_validator');
        $this->assertHasDefinition('scheb_two_factor.security.google.backup_code_validator');
        $this->assertHasAlias('scheb_two_factor.security.google.code_validator', 'scheb_two_factor.security.google.backup_code_validator');
    }

    /**
     * @test
     */
    public function load_emailAuthEnabled_loadEmailServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertHasDefinition('scheb_two_factor.auth_code_mailer');
        $this->assertHasDefinition('scheb_two_factor.security.email.code_generator');
        $this->assertHasDefinition('scheb_two_factor.security.email.provider');
        $this->assertHasDefinition('scheb_two_factor.security.email.email_code_validator');
        $this->assertHasDefinition('scheb_two_factor.security.email.backup_code_validator');
        $this->assertHasAlias('scheb_two_factor.security.email.code_validator', 'scheb_two_factor.security.email.backup_code_validator');
    }

    /**
     * @test
     */
    public function load_alternativeMailer_replaceArgument()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertDefinitionHasServiceArgument('scheb_two_factor.security.email.code_generator', 1, 'acme_test.mailer');
    }

    /**
     * @test
     */
    public function load_alternativePersister_replaceArguments()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertDefinitionHasServiceArgument('scheb_two_factor.trusted_computer_manager', 0, 'acme_test.persister');
        $this->assertDefinitionHasServiceArgument('scheb_two_factor.security.email.code_generator', 0, 'acme_test.persister');
        $this->assertDefinitionHasServiceArgument('scheb_two_factor.backup_code_validator', 0, 'acme_test.persister');
    }

    /**
     * @return array
     */
    private function getEmptyConfig()
    {
        $yaml = '';
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @return array
     */
    private function getFullConfig()
    {
        $yaml = <<<EOF
persister: acme_test.persister
model_manager_name: "alternative"
exclude_pattern: "excludePattern"
parameter_names:
    auth_code: authCodeName
    trusted: trustedName
security_tokens:
    - Symfony\Component\Security\Core\Authentication\Token\SomeToken
ip_whitelist:
    - 127.0.0.1
trusted_computer:
    enabled: true
    cookie_name: trusted_cookie
    cookie_lifetime: 2592000
    cookie_secure: true
    cookie_same_site: strict
email:
    enabled: true
    mailer: acme_test.mailer
    sender_email: me@example.com
    sender_name: Sender Name
    template: AcmeTestBundle:Authentication:emailForm.html.twig
    digits: 6
google:
    enabled: true
    issuer: Issuer
    server_name: Server Name
    template: AcmeTestBundle:Authentication:googleForm.html.twig
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->container->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @param string $id
     */
    private function assertHasDefinition($id)
    {
        $this->assertTrue($this->container->hasDefinition($id), 'Service "'.$id.'" must be defined.');
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse($this->container->hasDefinition($id), 'Service "'.$id.'" must NOT be defined.');
    }

    /**
     * @param string $id
     * @param string$alias
     */
    private function assertHasAlias($id, $alias)
    {
        $this->assertTrue($this->container->hasAlias($id));
        $this->assertEquals($alias, strval($this->container->getAlias($id)));
    }

    /**
     * @param string $id
     */
    private function assertDefinitionHasServiceArgument($id, $index, $expectedService)
    {
        $definition = $this->container->getDefinition($id);
        $argument = $definition->getArgument($index);
        $this->assertEquals($expectedService, strval($argument));
    }
}
