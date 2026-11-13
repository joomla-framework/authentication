<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests;

use Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy;
use Joomla\Authentication\Authentication;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\Password\RehashAwareHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy
 */
#[CoversClass(AbstractUsernamePasswordAuthenticationStrategy::class)]
class AbstractUsernamePasswordAuthenticationStrategyTest extends TestCase
{
    #[TestDox('A user can be successfully authenticated')]
    public function testAuthenticateSuccess()
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(true);

        $strategy = new class ($handler) extends AbstractUsernamePasswordAuthenticationStrategy {
            public function authenticate()
            {
                return $this->doAuthenticate('username', 'password');
            }

            protected function getHashedPassword($username)
            {
                return 'password';
            }
        };

        $this->assertSame('username', $strategy->authenticate());
        $this->assertSame(Authentication::SUCCESS, $strategy->getResult(), 'The correct result status is set');
    }

    #[TestDox('A user cannot be authenticated when a password cannot be found for the username')]
    public function testAuthenticateNoUser()
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->never())
            ->method('validatePassword');

        $strategy = new class ($handler) extends AbstractUsernamePasswordAuthenticationStrategy {
            public function authenticate()
            {
                return $this->doAuthenticate('username', 'password');
            }

            protected function getHashedPassword($username)
            {
                return false;
            }
        };

        $this->assertFalse($strategy->authenticate());
        $this->assertSame(Authentication::NO_SUCH_USER, $strategy->getResult(), 'The correct result status is set');
    }

    #[TestDox('A user cannot be authenticated when the password cannot be validated')]
    public function testAuthenticateInvalidPassword()
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(false);

        $strategy = new class ($handler) extends AbstractUsernamePasswordAuthenticationStrategy {
            public function authenticate()
            {
                return $this->doAuthenticate('username', 'password');
            }

            protected function getHashedPassword($username)
            {
                return 'password';
            }
        };

        $this->assertFalse($strategy->authenticate());
        $this->assertSame(Authentication::INVALID_CREDENTIALS, $strategy->getResult(), 'The correct result status is set');
    }

    #[TestDox('An outdated hash is passed to the rehash hook after a successful login')]
    public function testAuthenticateRehashesOutdatedHash()
    {
        $handler = $this->createMock(RehashAwareHandlerInterface::class);
        $handler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(true);
        $handler->expects($this->once())
            ->method('needsRehash')
            ->with('hash')
            ->willReturn(true);

        $strategy = $this->createRehashRecordingStrategy($handler);

        $this->assertSame('username', $strategy->authenticate());
        $this->assertSame([['username', 'password']], $strategy->rehashed, 'The rehash hook is called with the credentials');
        $this->assertSame(Authentication::SUCCESS, $strategy->getResult(), 'The correct result status is set');
    }

    #[TestDox('A current hash is not rehashed')]
    public function testAuthenticateKeepsCurrentHash()
    {
        $handler = $this->createMock(RehashAwareHandlerInterface::class);
        $handler->method('validatePassword')->willReturn(true);
        $handler->method('needsRehash')->willReturn(false);

        $strategy = $this->createRehashRecordingStrategy($handler);

        $this->assertSame('username', $strategy->authenticate());
        $this->assertSame([], $strategy->rehashed, 'The rehash hook is not called');
    }

    #[TestDox('A failed login is not rehashed')]
    public function testAuthenticateInvalidPasswordIsNotRehashed()
    {
        $handler = $this->createMock(RehashAwareHandlerInterface::class);
        $handler->method('validatePassword')->willReturn(false);
        $handler->expects($this->never())
            ->method('needsRehash');

        $strategy = $this->createRehashRecordingStrategy($handler);

        $this->assertFalse($strategy->authenticate());
        $this->assertSame([], $strategy->rehashed, 'The rehash hook is not called');
    }

    private function createRehashRecordingStrategy(HandlerInterface $handler): AbstractUsernamePasswordAuthenticationStrategy
    {
        return new class ($handler) extends AbstractUsernamePasswordAuthenticationStrategy {
            public array $rehashed = [];

            public function authenticate()
            {
                return $this->doAuthenticate('username', 'password');
            }

            protected function getHashedPassword($username)
            {
                return 'hash';
            }

            protected function rehashPassword(string $username, string $password): void
            {
                $this->rehashed[] = [$username, $password];
            }
        };
    }

}
