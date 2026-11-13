<?php

/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2026 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

/**
 * Interface defining a password handler that can tell whether a hash should be upgraded
 *
 * @since  __DEPLOY_VERSION__
 */
interface RehashAwareHandlerInterface extends HandlerInterface
{
    /**
     * Check whether a hash was not created with this handler's algorithm and options and should be replaced
     *
     * @param   string  $hash     The password hash to check
     * @param   array   $options  The options a new hash would be created with
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function needsRehash(string $hash, array $options = []): bool;
}
