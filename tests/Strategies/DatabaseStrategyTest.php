<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests\Strategies;

use Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy;
use Joomla\Authentication\Authentication;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\Strategies\DatabaseStrategy;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\Input\Input;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Authentication\Strategies\DatabaseStrategy
 */
#[CoversClass(DatabaseStrategy::class)]
#[UsesClass(AbstractUsernamePasswordAuthenticationStrategy::class)]
class DatabaseStrategyTest extends TestCase
{
    /**
     * @var  MockObject|DatabaseDriver
     */
    private $db;

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
        $this->db              = $this->createMock(DatabaseInterface::class);
        $this->input           = $this->createMock(Input::class);
        $this->passwordHandler = $this->createMock(HandlerInterface::class);

        parent::setUp();
    }

    /**
     * Tests the authenticate method with valid credentials.
     */
    public function testValidPassword()
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects($this->once())
            ->method('select')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('from')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('bind')
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('createQuery')
            ->willReturn($query);

        $this->db->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('loadResult')
            ->willReturn('$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJG');

        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(true);

        $strategy = new DatabaseStrategy($this->input, $this->db, [], $this->passwordHandler);

        $this->assertEquals('username', $strategy->authenticate());
        $this->assertEquals(Authentication::SUCCESS, $strategy->getResult());
    }

    /**
     * Tests the authenticate method with invalid credentials.
     */
    public function testInvalidPassword()
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects($this->once())
            ->method('select')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('from')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('bind')
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('createQuery')
            ->willReturn($query);

        $this->db->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('loadResult')
            ->willReturn('$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH');

        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->willReturn(false);

        $strategy = new DatabaseStrategy($this->input, $this->db, [], $this->passwordHandler);

        $this->assertEquals(false, $strategy->authenticate());
        $this->assertEquals(Authentication::INVALID_CREDENTIALS, $strategy->getResult());
    }

    /**
     * Tests the authenticate method with no credentials provided.
     */
    public function testNoPassword()
    {
        $this->db->expects($this->never())
            ->method('setQuery');

        $this->input->expects($this->exactly(2))
            ->method('get')
            ->willReturn(false);

        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $strategy = new DatabaseStrategy($this->input, $this->db, [], $this->passwordHandler);

        $this->assertEquals(false, $strategy->authenticate());
        $this->assertEquals(Authentication::NO_CREDENTIALS, $strategy->getResult());
    }

    /**
     * Tests the authenticate method with credentials for an unknown user.
     */
    public function testUserNotExist()
    {
        $query = $this->createMock(QueryInterface::class);
        $query->expects($this->once())
            ->method('select')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('from')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $query->expects($this->once())
            ->method('bind')
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('createQuery')
            ->willReturn($query);

        $this->db->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();

        $this->db->expects($this->once())
            ->method('loadResult')
            ->willReturn(null);

        $this->input->expects($this->exactly(3))
            ->method('get')
            ->willReturnArgument(0);

        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $strategy = new DatabaseStrategy($this->input, $this->db, [], $this->passwordHandler);

        $this->assertEquals(false, $strategy->authenticate());
        $this->assertEquals(Authentication::NO_SUCH_USER, $strategy->getResult());
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
        $this->db->expects($this->never())
            ->method('setQuery');

        $this->passwordHandler->expects($this->never())
            ->method('validatePassword');

        $strategy = new DatabaseStrategy(new Input($data), $this->db, [], $this->passwordHandler);

        $this->assertFalse($strategy->authenticate());
        $this->assertSame(Authentication::INVALID_CREDENTIALS, $strategy->getResult());
    }

    /**
     * Tests the authenticate method accepts the username and password "0".
     */
    public function testZeroCredentials()
    {
        $query = $this->createMock(QueryInterface::class);
        $query->method('select')->willReturnSelf();
        $query->method('from')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->expects($this->once())
            ->method('bind')
            ->with(1, '0')
            ->willReturnSelf();

        $this->db->method('createQuery')->willReturn($query);
        $this->db->method('setQuery')->willReturnSelf();
        $this->db->method('loadResult')->willReturn('hash');

        $this->passwordHandler->expects($this->once())
            ->method('validatePassword')
            ->with('0', 'hash')
            ->willReturn(true);

        $strategy = new DatabaseStrategy(new Input(['username' => '0', 'password' => '0']), $this->db, [], $this->passwordHandler);

        $this->assertSame('0', $strategy->authenticate());
        $this->assertSame(Authentication::SUCCESS, $strategy->getResult());
    }

}
