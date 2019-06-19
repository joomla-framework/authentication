<?php
/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests\Strategies;

use Joomla\Authentication\Authentication;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\Strategies\DatabaseStrategy;
use Joomla\Authentication\Tests\DatabaseManager;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Input\Input;
use Joomla\Test\DatabaseManager as BaseDatabaseManager;
use Joomla\Test\DatabaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test class for \Joomla\Authentication\Strategies\DatabaseStrategy
 */
class DatabaseStrategyTest extends DatabaseTestCase
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
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		try
		{
			static::getDatabaseManager()->loadSchema();
		}
		catch (ConnectionFailureException $exception)
		{
			static::markTestSkipped('Could not connect to the test database, cannot run database tests.');
		}
		catch (ExecutionFailureException $exception)
		{
			static::markTestSkipped('Could not load the schema to the test database, cannot run database tests.');
		}
	}

	/**
	 * Create the database manager for this test class.
	 *
	 * If necessary, this method can be extended to create your own subclass of the base DatabaseManager object to customise
	 * the behaviors in your application.
	 *
	 * @return  DatabaseManager
	 */
	protected static function createDatabaseManager(): BaseDatabaseManager
	{
		return new DatabaseManager;
	}

	/**
	 * Inserts a user into the test database
	 *
	 * @param   string  $username  Test username
	 * @param   string  $password  Test hashed password
	 *
	 * @return  void
	 */
	private function addUser($username, $password): void
	{
		// Insert the user into the table
		$db = static::$connection;

		$db->setQuery(
			$db->getQuery(true)
				->insert('#__users')
				->columns(['username', 'password'])
				->values(':username, :password')
				->bind(':username', $username)
				->bind(':password', $password)
		)->execute();
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 */
	protected function setUp(): void
	{
		$this->input           = $this->createMock(Input::class);
		$this->passwordHandler = $this->createMock(HandlerInterface::class);
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 */
	protected function tearDown(): void
	{
		static::getDatabaseManager()->clearTables();
	}

	/**
	 * Tests the authenticate method with valid credentials.
	 */
	public function testValidPassword()
	{
		$this->input->expects($this->any())
			->method('get')
			->willReturnArgument(0);

		$this->passwordHandler->expects($this->any())
			->method('validatePassword')
			->willReturn(true);

		$this->addUser('username', '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJG');

		$strategy = new DatabaseStrategy($this->input, static::$connection, [], $this->passwordHandler);

		$this->assertEquals('username', $strategy->authenticate());
		$this->assertEquals(Authentication::SUCCESS, $strategy->getResult());
	}

	/**
	 * Tests the authenticate method with invalid credentials.
	 */
	public function testInvalidPassword()
	{
		$this->input->expects($this->any())
			->method('get')
			->willReturnArgument(0);

		$this->passwordHandler->expects($this->any())
			->method('validatePassword')
			->willReturn(false);

		$this->addUser('username', '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH');

		$strategy = new DatabaseStrategy($this->input, static::$connection, [], $this->passwordHandler);

		$this->assertEquals(false, $strategy->authenticate());
		$this->assertEquals(Authentication::INVALID_CREDENTIALS, $strategy->getResult());
	}

	/**
	 * Tests the authenticate method with no credentials provided.
	 */
	public function testNoPassword()
	{
		$this->input->expects($this->any())
			->method('get')
			->willReturn(false);

		$this->passwordHandler->expects($this->never())
			->method('validatePassword');

		$this->addUser('username', '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH');

		$strategy = new DatabaseStrategy($this->input, static::$connection, [], $this->passwordHandler);

		$this->assertEquals(false, $strategy->authenticate());
		$this->assertEquals(Authentication::NO_CREDENTIALS, $strategy->getResult());
	}

	/**
	 * Tests the authenticate method with credentials for an unknown user.
	 */
	public function testUserNotExist()
	{
		$this->input->expects($this->any())
			->method('get')
			->willReturnArgument(0);

		$this->passwordHandler->expects($this->never())
			->method('validatePassword');

		$this->addUser('jimbob', '$2y$10$.vpEGa99w.WUetDFJXjMn.RiKRhZ/ImzxtOjtoJ0VFDV8S7ua0uJH');

		$strategy = new DatabaseStrategy($this->input, static::$connection, [], $this->passwordHandler);

		$this->assertEquals(false, $strategy->authenticate());
		$this->assertEquals(Authentication::NO_SUCH_USER, $strategy->getResult());
	}
}
