<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests\Strategies;

use Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy;
use Joomla\Authentication\Authentication;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\Strategies\LocalStrategy;
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\Authentication\Strategies\LocalStrategy
 */
#[CoversClass(LocalStrategy::class)]
#[UsesClass(AbstractUsernamePasswordAuthenticationStrategy::class)]
class LocalStrategyTest extends TestCase
{
    /**
     * @var  MockObject|Input
     */
    private $input;

    /**
     * @var  MockObject|HandlerInterface
     */
    private $passwordHandler;

    /**
     * Sets up the fixture, for example, opens a network connection.
     */
    protected function setUp(): void
    {
        $this->input           = $this->createMock(Input::class);
        $this->passwordHandler = $this->createMock(HandlerInterface::class);

        parent::setUp();
    }

    /**
     * Tests the authenticate method with valid credentials.
     */
    public function testValidPassword()
    {
        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(true);

        $credentialStore = [
            'username' => '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJG',
        ];

        $localStrategy = new LocalStrategy($this->input, $credentialStore, $this->passwordHandler);

        $this->assertEquals('username', $localStrategy->authenticate());

        $this->assertEquals(Authentication::SUCCESS, $localStrategy->getResult());
    }

    /**
     * Tests the authenticate method with invalid credentials.
     */
    public function testInvalidPassword()
    {
        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(false);

        $credentialStore = [
            'username' => '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH',
        ];

        $localStrategy = new LocalStrategy($this->input, $credentialStore, $this->passwordHandler);

        $this->assertEquals(false, $localStrategy->authenticate());

        $this->assertEquals(Authentication::INVALID_CREDENTIALS, $localStrategy->getResult());
    }

    /**
     * Tests the authenticate method with no credentials provided.
     */
    public function testNoPassword()
    {
        $this->input->expects($this->exactly(2))
            ->method('get')
            ->willReturn(false);

        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $credentialStore = [
            'username' => '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH',
        ];

        $localStrategy = new LocalStrategy($this->input, $credentialStore, $this->passwordHandler);

        $this->assertEquals(false, $localStrategy->authenticate());

        $this->assertEquals(Authentication::NO_CREDENTIALS, $localStrategy->getResult());
    }

    /**
     * Tests the authenticate method with credentials for an unknown user.
     */
    public function testUserNotExist()
    {
        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $credentialStore = [
            'jimbob' => '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH',
        ];

        $localStrategy = new LocalStrategy($this->input, $credentialStore, $this->passwordHandler);

        $this->assertEquals(false, $localStrategy->authenticate());

        $this->assertEquals(Authentication::NO_SUCH_USER, $localStrategy->getResult());
    }

    /**
     * Data provider for credentials that must be rejected without a lookup.
     *
     * @return  \Generator
     */
    public static function rejectedCredentialsProvider(): \Generator
    {
        yield 'password is an array' => [['username' => 'bob', 'password' => ['secret']]];
        yield 'username is an array' => [['username' => ['bob'], 'password' => 'secret']];
        yield 'username changed by the username filter' => [['username' => 'b<o>b', 'password' => 'secret']];
        yield 'username with a control character' => [['username' => "bob\n", 'password' => 'secret']];
    }

    /**
     * Tests the authenticate method rejects malformed credentials instead of erroring or rewriting them.
     */
    #[DataProvider('rejectedCredentialsProvider')]
    public function testRejectedCredentials(array $data)
    {
        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $localStrategy = new LocalStrategy(new Input($data), ['bob' => 'hash'], $this->passwordHandler);

        $this->assertFalse($localStrategy->authenticate());
        $this->assertSame(Authentication::INVALID_CREDENTIALS, $localStrategy->getResult());
    }

    /**
     * Tests the authenticate method accepts the username and password "0".
     */
    public function testZeroCredentials()
    {
        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->with('0', 'hash')
            ->willReturn(true);

        $localStrategy = new LocalStrategy(new Input(['username' => '0', 'password' => '0']), ['0' => 'hash'], $this->passwordHandler);

        $this->assertSame('0', $localStrategy->authenticate());
        $this->assertSame(Authentication::SUCCESS, $localStrategy->getResult());
    }

}
