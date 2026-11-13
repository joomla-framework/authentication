<?php

/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Password;

use Joomla\Authentication\Exception\UnsupportedPasswordHandlerException;

/**
 * Password handler for Argon2id hashed passwords
 *
 * @since  1.3.0
 */
class Argon2idHandler implements RehashAwareHandlerInterface
{
    /**
     * Generate a hash for a plaintext password
     *
     * @param   string  $plaintext  The plaintext password to validate
     * @param   array   $options    Options for the hashing operation
     *
     * @return  string
     *
     * @since   1.3.0
     * @throws  UnsupportedPasswordHandlerException if the password handler is not supported
     */
    public function hashPassword($plaintext, array $options = [])
    {
        // Use the password extension if able
        if (self::isSupported()) {
            return password_hash($plaintext, \PASSWORD_ARGON2ID, $options);
        }

        throw new UnsupportedPasswordHandlerException('Argon2id algorithm is not supported.');
    }

    /**
     * Check that the password handler is supported in this environment
     *
     * @return  boolean
     *
     * @since   1.3.0
     */
    public static function isSupported()
    {
        // Check for native PHP engine support in the password extension
        if (\defined('PASSWORD_ARGON2ID')) {
            return true;
        }

        return false;
    }

    /**
     * Check whether a hash was not created with this handler's algorithm and options and should be replaced
     *
     * @param   string  $hash     The password hash to check
     * @param   array   $options  The options a new hash would be created with
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     * @throws  UnsupportedPasswordHandlerException if the password handler is not supported
     */
    public function needsRehash(string $hash, array $options = []): bool
    {
        if (!self::isSupported()) {
            throw new UnsupportedPasswordHandlerException('Argon2id algorithm is not supported.');
        }

        return password_needs_rehash($hash, \PASSWORD_ARGON2ID, $options);
    }

    /**
     * Validate a password
     *
     * @param   string  $plaintext  The plain text password to validate
     * @param   string  $hashed     The password hash to validate against
     *
     * @return  boolean
     *
     * @since   1.3.0
     * @throws  UnsupportedPasswordHandlerException if the password handler is not supported
     */
    public function validatePassword($plaintext, $hashed)
    {
        // Use the password extension if able
        if (self::isSupported()) {
            return password_verify($plaintext, $hashed);
        }

        throw new UnsupportedPasswordHandlerException('Argon2id algorithm is not supported.');
    }
}
