<?php

/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

/**
 * Interface defining a password handler
 *
 * @since  1.2.0
 */
interface HandlerInterface
{
    /**
     * Generate a hash for a plaintext password
     *
     * @param   string  $plaintext  The plaintext password to validate
     * @param   array   $options    Options for the hashing operation
     *
     * @return  string
     *
     * @since   1.2.0
     */
    public function hashPassword(string $plaintext, array $options = []): string;

    /**
     * Check that the password handler is supported in this environment
     *
     * @return  boolean
     *
     * @since   1.2.0
     */
    public static function isSupported(): bool;

    /**
     * Validate a password
     *
     * @param   string  $plaintext  The plain text password to validate
     * @param   string  $hashed     The password hash to validate against
     *
     * @return  boolean
     *
     * @since   1.2.0
     */
    public function validatePassword(string $plaintext, string $hashed): bool;

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
