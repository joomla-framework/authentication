<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests;

use Joomla\Authentication\Authentication;
use Joomla\Authentication\AuthenticationStrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Authentication\Authentication
 */
#[CoversClass(Authentication::class)]
class AuthenticationTest extends TestCase
{
    /**
     * Object being tested
     *
     * @var  Authentication
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        $this->object = new Authentication();
    }

    /**
     * Tests the authenticate method, specifying the strategy by name.
     */
    public function testSingleStrategy()
    {
        $mockStrategy = $this->createMock(AuthenticationStrategyInterface::class);

        $mockStrategy->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);

        $this->object->addStrategy('mock', $mockStrategy);

        $this->assertFalse($this->object->authenticate(['mock']));
    }

    /**
     * Tests the authenticate method, using all strategies
     */
    public function testSingleStrategyEmptyArray()
    {
        $mockStrategy = $this->createMock(AuthenticationStrategyInterface::class);

        $mockStrategy->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);

        $this->object->addStrategy('mock', $mockStrategy);

        $this->assertFalse($this->object->authenticate());
    }

    /**
     * Tests the authenticate method, using some strategies.
     */
    public function testSomeStrategies()
    {
        $mockStrategy1 = $this->createMock(AuthenticationStrategyInterface::class);

        $mockStrategy1->expects($this->never())
            ->method('authenticate');

        $mockStrategy2 = $this->createMock(AuthenticationStrategyInterface::class);

        $mockStrategy2->expects($this->once())
            ->method('authenticate')
            ->willReturn('jimbob');

        $mockStrategy3 = $this->createMock(AuthenticationStrategyInterface::class);

        $this->object->addStrategy('mock1', $mockStrategy1);
        $this->object->addStrategy('mock2', $mockStrategy2);
        $this->object->addStrategy('mock3', $mockStrategy3);

        $mockStrategy3->expects($this->never())
            ->method('authenticate');

        $this->assertEquals('jimbob', $this->object->authenticate(['mock2', 'mock3']));
    }

    /**
     * Tests the authenticate method, using a non registered strategy
     */
    public function testStrategiesException()
    {
        $this->expectException(\RuntimeException::class);

        $this->object->authenticate(['mock1']);
    }

    /**
     * Tests getting the result back.
     */
    public function testGetResults()
    {
        $mockStrategy = $this->createMock(AuthenticationStrategyInterface::class);

        $mockStrategy->expects($this->once())
            ->method('authenticate')
            ->willReturn(false);

        $mockStrategy->expects($this->once())
            ->method('getResult')
            ->willReturn(Authentication::SUCCESS);

        $this->object->addStrategy('mock', $mockStrategy);

        $this->object->authenticate();

        $this->assertEquals(
            ['mock' => Authentication::SUCCESS],
            $this->object->getResults()
        );
    }

    /**
     * Tests that the results only describe the last authentication attempt.
     */
    public function testGetResultsAreResetOnEachAttempt()
    {
        $strategyA = $this->createMock(AuthenticationStrategyInterface::class);
        $strategyA->method('authenticate')->willReturn(false);
        $strategyA->method('getResult')->willReturn(Authentication::NO_SUCH_USER);

        $strategyB = $this->createMock(AuthenticationStrategyInterface::class);
        $strategyB->method('authenticate')->willReturn(false);
        $strategyB->method('getResult')->willReturn(Authentication::NO_CREDENTIALS);

        $this->object->addStrategy('a', $strategyA);
        $this->object->addStrategy('b', $strategyB);

        $this->object->authenticate();
        $this->object->authenticate(['a']);

        $this->assertSame(['a' => Authentication::NO_SUCH_USER], $this->object->getResults());
    }

    /**
     * Tests that strategies after a successful one are not tried and have no result.
     */
    public function testGetResultsStopAtTheFirstSuccess()
    {
        $strategyA = $this->createMock(AuthenticationStrategyInterface::class);
        $strategyA->method('authenticate')->willReturn('user');
        $strategyA->method('getResult')->willReturn(Authentication::SUCCESS);

        $strategyB = $this->createMock(AuthenticationStrategyInterface::class);
        $strategyB->expects($this->never())->method('authenticate');

        $this->object->addStrategy('a', $strategyA);
        $this->object->addStrategy('b', $strategyB);

        $this->assertSame('user', $this->object->authenticate());
        $this->assertSame(['a' => Authentication::SUCCESS], $this->object->getResults());
    }

}
