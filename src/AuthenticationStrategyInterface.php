<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later; see LICENSE
 */

namespace Joomla\Authentication;

/**
 * Joomla Framework AuthenticationStrategy Interface
 *
 * @since  1.0
 */
interface AuthenticationStrategyInterface
{
	/**
	 * Attempt authentication.
	 *
	 * @return  string|boolean  A string containing a username if authentication is successful, false otherwise.
	 *
	 * @since   1.0
	 */
	public function authenticate();

	/**
	 * Get last authentication result.
	 *
	 * @return  integer  An integer from Authentication class constants with the authentication result.
	 *
	 * @since   1.0
	 */
	public function getResult();
}
