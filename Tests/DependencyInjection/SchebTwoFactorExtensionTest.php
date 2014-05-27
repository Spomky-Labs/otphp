<?php
namespace Scheb\TwoFactorBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;
use Scheb\TwoFactorBundle\DependencyInjection\SchebTwoFactorExtension;

class SchebTwoFactorExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var \Scheb\TwoFactorBundle\DependencyInjection\SchebTwoFactorExtension
     */
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new SchebTwoFactorExtension();
    }

    /**
     * @test
     */
    public function load_emptyConfig_setDefaultValues()
    {
        $config = $this->getEmptyConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertParameter(null, "scheb_two_factor.model_manager_name");
        $this->assertParameter("no-reply@example.com", "scheb_two_factor.email.sender_email");
        $this->assertParameter("SchebTwoFactorBundle:Authentication:form.html.twig", "scheb_two_factor.email.template");
        $this->assertParameter(4, "scheb_two_factor.email.digits");
        $this->assertParameter(null, "scheb_two_factor.google.server_name");
        $this->assertParameter("SchebTwoFactorBundle:Authentication:form.html.twig", "scheb_two_factor.google.template");
        $this->assertParameter(false, "scheb_two_factor.trusted_computer.enabled");
        $this->assertParameter("trusted_computer", "scheb_two_factor.trusted_computer.cookie_name");
        $this->assertParameter(5184000, "scheb_two_factor.trusted_computer.cookie_lifetime");
        $this->assertParameter(array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken"), "scheb_two_factor.security_tokens");
    }

    /**
     * @test
     */
    public function load_fullConfig_setConfigValues()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertParameter("alternative", "scheb_two_factor.model_manager_name");
        $this->assertParameter("me@example.com", "scheb_two_factor.email.sender_email");
        $this->assertParameter("AcmeTestBundle:Authentication:emailForm.html.twig", "scheb_two_factor.email.template");
        $this->assertParameter(6, "scheb_two_factor.email.digits");
        $this->assertParameter("Server Name", "scheb_two_factor.google.server_name");
        $this->assertParameter("AcmeTestBundle:Authentication:googleForm.html.twig", "scheb_two_factor.google.template");
        $this->assertParameter(true, "scheb_two_factor.trusted_computer.enabled");
        $this->assertParameter("trusted_cookie", "scheb_two_factor.trusted_computer.cookie_name");
        $this->assertParameter(2592000, "scheb_two_factor.trusted_computer.cookie_lifetime");
        $this->assertParameter(array("Symfony\Component\Security\Core\Authentication\Token\SomeToken"), "scheb_two_factor.security_tokens");
    }

    /**
     * @test
     */
    public function load_emptyConfig_loadBasicServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        //Security
        $this->assertHasDefinition("scheb_two_factor.session_flag_manager");
        $this->assertHasDefinition("scheb_two_factor.session_flag_generator");
        $this->assertHasDefinition("scheb_two_factor.trusted_cookie_manager");
        $this->assertHasDefinition("scheb_two_factor.trusted_token_generator");
        $this->assertHasDefinition("scheb_two_factor.provider_registry");

        //Doctrine
        $this->assertHasDefinition("scheb_two_factor.entity_manager");

        //Listeners
        $this->assertHasDefinition("scheb_two_factor.security.interactive_login_listener");
        $this->assertHasDefinition("scheb_two_factor.security.request_listener");
    }

    /**
     * @test
     */
    public function load_noAuthEnabled_notLoadServices()
    {
        $config = $this->getEmptyConfig();
        $this->extension->load(array($config), $this->container);

        //Google
        $this->assertNotHasDefinition("scheb_two_factor.security.google");
        $this->assertNotHasDefinition("scheb_two_factor.security.google_authenticator");
        $this->assertNotHasDefinition("scheb_two_factor.security.google.provider");

        //Email
        $this->assertNotHasDefinition("scheb_two_factor.auth_code_mailer");
        $this->assertNotHasDefinition("scheb_two_factor.security.email.code_manager");
        $this->assertNotHasDefinition("scheb_two_factor.security.email.provider");
    }

    /**
     * @test
     */
    public function load_googleAuthEnabled_loadGoogleServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertHasDefinition("scheb_two_factor.security.google");
        $this->assertHasDefinition("scheb_two_factor.security.google_authenticator");
        $this->assertHasDefinition("scheb_two_factor.security.google.provider");
    }

    /**
     * @test
     */
    public function load_emailAuthEnabled_loadEmailServices()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $this->assertHasDefinition("scheb_two_factor.auth_code_mailer");
        $this->assertHasDefinition("scheb_two_factor.security.email.code_manager");
        $this->assertHasDefinition("scheb_two_factor.security.email.provider");
    }

    /**
     * @test
     */
    public function load_alternativeMailer_replaceArgument()
    {
        $config = $this->getFullConfig();
        $this->extension->load(array($config), $this->container);

        $definition = $this->container->getDefinition("scheb_two_factor.security.email.code_manager");
        $reference = $definition->getArgument(1);
        $this->assertEquals("acme_test.mailer", strval($reference));
    }

    /**
     * @return array
     */
    private function getEmptyConfig()
    {
        $yaml = "";
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    /**
     * @return array
     */
    private function getFullConfig()
    {
        $yaml = <<<EOF
model_manager_name: "alternative"
security_tokens:
    - Symfony\Component\Security\Core\Authentication\Token\SomeToken
trusted_computer:
    enabled: true
    cookie_name: trusted_cookie
    cookie_lifetime: 2592000
email:
    enabled: true
    mailer: acme_test.mailer
    sender_email: me@example.com
    template: AcmeTestBundle:Authentication:emailForm.html.twig
    digits: 6
google:
    enabled: true
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
        $this->assertTrue(($this->container->hasDefinition($id)));
    }

    /**
     * @param string $id
     */
    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->container->hasDefinition($id)));
    }

}
