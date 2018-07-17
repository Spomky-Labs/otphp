<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Tests\Security\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Authentication\AuthenticationTrustResolver;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationTrustResolverTest extends TestCase
{
    /**
     * @var MockObject|AuthenticationTrustResolverInterface
     */
    private $decoratedTrustResolver;

    /**
     * @var AuthenticationTrustResolver
     */
    private $trustResolver;

    protected function setUp()
    {
        $this->decoratedTrustResolver = $this->createMock(AuthenticationTrustResolverInterface::class);
        $this->trustResolver = new AuthenticationTrustResolver($this->decoratedTrustResolver);
    }

    public function provideReturnedResult(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @test
     * @dataProvider provideReturnedResult
     */
    public function isAnonymous_tokenGiven_returnResultFromDecoratedTrustResolver($returnedResult)
    {
        $this->decoratedTrustResolver
            ->expects($this->once())
            ->method('isAnonymous')
            ->willReturn($returnedResult);

        $returnValue = $this->trustResolver->isAnonymous($this->createMock(TokenInterface::class));
        $this->assertEquals($returnedResult, $returnValue);
    }

    /**
     * @test
     * @dataProvider provideReturnedResult
     */
    public function isRememberMe_tokenGiven_returnResultFromDecoratedTrustResolver($returnedResult)
    {
        $this->decoratedTrustResolver
            ->expects($this->once())
            ->method('isRememberMe')
            ->willReturn($returnedResult);

        $returnValue = $this->trustResolver->isRememberMe($this->createMock(TokenInterface::class));
        $this->assertEquals($returnedResult, $returnValue);
    }

    /**
     * @test
     */
    public function isFullFledged_twoFactorToken_returnFalse()
    {
        $this->decoratedTrustResolver
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->trustResolver->isFullFledged($this->createMock(TwoFactorToken::class));
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider provideReturnedResult
     */
    public function isFullFledged_notTwoFactorToken_returnResultFromDecoratedTrustResolver($returnedResult)
    {
        $this->decoratedTrustResolver
            ->expects($this->once())
            ->method('isFullFledged')
            ->willReturn($returnedResult);

        $returnValue = $this->trustResolver->isFullFledged($this->createMock(TokenInterface::class));
        $this->assertEquals($returnedResult, $returnValue);
    }
}
