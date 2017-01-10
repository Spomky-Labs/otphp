<?php
namespace Scheb\TwoFactorBundle\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns a test double for the specified class.
     * Compatibility layer for PHPUnit < 5.3
     *
     * @param string $originalClassName
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @throws \PHPUnit_Framework_Exception
     */
    protected function createMock($originalClassName)
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
    }
}
