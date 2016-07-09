<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class AuthenticationContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $token;

    /**
     * @var AuthenticationContext
     */
    private $authContext;

    public function setUp()
    {
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->authContext = new AuthenticationContext($this->request, $this->token);
    }

    /**
     * @test
     */
    public function getToken_objectInitialized_returnToken()
    {
        $returnValue = $this->authContext->getToken();
        $this->assertEquals($this->token, $returnValue);
    }

    /**
     * @test
     */
    public function getRequest_objectInitialized_returnRequest()
    {
        $returnValue = $this->authContext->getRequest();
        $this->assertEquals($this->request, $returnValue);
    }

    /**
     * @test
     */
    public function getSession_objectInitialized_returnSession()
    {
        //Mock the Request object
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $this->request
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session));

        $returnValue = $this->authContext->getSession();
        $this->assertEquals($session, $returnValue);
    }

    /**
     * @test
     * @dataProvider dataProvider_getToken
     */
    public function getUser_objectInitialized_returnValid($userObject, $expectedReturnValue)
    {
        //Mock the TokenInterface
        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($userObject));

        $returnValue = $this->authContext->getUser();
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    public function dataProvider_getToken()
    {
        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');

        return array(
            array($user, $user),
            array(null, null),
            array('anon.', null),
        );
    }

    /**
     * @test
     */
    public function useTrustedOption_trustedOptionEnabled_returnTrue()
    {
        $this->authContext->setUseTrustedOption(true);
        $returnValue = $this->authContext->useTrustedOption();
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function useTrustedOption_trustedOptionDisabled_returnFalse()
    {
        $this->authContext->setUseTrustedOption(false);
        $returnValue = $this->authContext->useTrustedOption();
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function isAuthenticated_notAuthenticated_returnFalse()
    {
        $returnValue = $this->authContext->isAuthenticated();
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function setAuthenticated_wasNotAuthenticated_becomeAuthenticated()
    {
        $this->authContext->setAuthenticated(true);
        $returnValue = $this->authContext->isAuthenticated();
        $this->assertTrue($returnValue);
    }
}
