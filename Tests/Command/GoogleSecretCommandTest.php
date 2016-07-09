<?php

namespace Scheb\TwoFactorBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Scheb\TwoFactorBundle\Command\GoogleSecretCommand;

class GoogleSecretCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GoogleSecretCommand
     */
    private $command;

    public function setUp()
    {
        $application = new Application();
        $application->add(new GoogleSecretCommand());
        $this->command = $application->find('scheb:two-factor:google-secret');
    }

    private function createContainerStub($hasService, $googleAuthenticator)
    {
        //Stub the container
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('has')
            ->with('scheb_two_factor.security.google_authenticator')
            ->will($this->returnValue($hasService));
        $container
            ->expects($this->any())
            ->method('get')
            ->with('scheb_two_factor.security.google_authenticator')
            ->will($this->returnValue($googleAuthenticator));

        return $container;
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function execute_googleAuthenticationDisabled_throwException()
    {
        $container = $this->createContainerStub(false, null);
        $this->command->setContainer($container);
        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->createMock('Symfony\Component\Console\Output\OutputInterface');
        $this->command->execute($input, $output);
    }

    /**
     * @test
     */
    public function execute_googleAuthenticationEnabled_returnSecret()
    {
        //Mock the GoogleAuthenticator
        $googleAuthenticator = $this->getMockBuilder('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator')
            ->disableOriginalConstructor()
            ->getMock();
        $googleAuthenticator
            ->expects($this->once())
            ->method('generateSecret')
            ->will($this->returnValue('secretCode'));

        //Stub the container to return GoogleAuthenticator
        $container = $this->createContainerStub(true, $googleAuthenticator);
        $this->command->setContainer($container);

        $input = $this->createMock('Symfony\Component\Console\Input\InputInterface');
        $output = $this->createMock('Symfony\Component\Console\Output\OutputInterface');

        //Expect some output
        $output
            ->expects($this->once())
            ->method('writeln')
            ->with('<info>Secret:</info> secretCode');

        $this->command->execute($input, $output);
    }
}
