<?php
/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests;

use Joomla\Test\DatabaseManager as BaseDatabaseManager;

/**
 * Extended database manager to handle configuring the package's test database
 */
class DatabaseManager extends BaseDatabaseManager
{
	/**
	 * Loads the schema into the database
	 *
	 * @return void
	 */
	public function loadSchema(): void
	{
		$db = $this->getConnection();

		$schema = <<<SQL
CREATE TABLE `#__users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` TEXT NOT NULL DEFAULT '',
  `password` TEXT NOT NULL DEFAULT ''
);

CREATE INDEX `idx_users_username` ON `#__users` (`username`);
CREATE INDEX `idx_users_email` ON `#__users` (`email`);
SQL;

		$db->setQuery($schema)->execute();
	}

	/**
	 * Initialize the parameter storage for the database connection
	 *
	 * Overrides the behavior of the parent class to force an in-memory SQLite database for testing
	 *
	 * @return  void
	 */
	protected function initialiseParams(): void
	{
		if (empty($this->params))
		{
			$this->params = [
				'driver'   => 'sqlite',
				'database' => ':memory:',
				'user'     => null,
			];
		}
	}
}
