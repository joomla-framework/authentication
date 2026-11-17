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
 * Password handler for Argon2i hashed passwords
 *
 * @since  1.2.0
 */
class Argon2iHandler implements HandlerInterface
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
     * @throws  UnsupportedPasswordHandlerException if the password handler is not supported
     */
    public function hashPassword($plaintext, array $options = [])
    {
        if (self::isSupported()) {
            return password_hash($plaintext, \PASSWORD_ARGON2I, $options);
        }

        throw new UnsupportedPasswordHandlerException('Argon2i algorithm is not supported.');
    }

    /**
     * Check that the password handler is supported in this environment
     *
     * @return  boolean
     *
     * @since   1.2.0
     */
    public static function isSupported()
    {
        // PHP defines the constant when it supports Argon2i, through libargon2 or ext-sodium
        return \defined('PASSWORD_ARGON2I');
    }

    /**
     * Validate a password
     *
     * @param   string  $plaintext  The plain text password to validate
     * @param   string  $hashed     The password hash to validate against
     *
     * @return  boolean
     *
     * @since   1.2.0
     * @throws  UnsupportedPasswordHandlerException if the password handler is not supported
     */
    public function validatePassword($plaintext, $hashed)
    {
        if (self::isSupported()) {
            return password_verify($plaintext, $hashed);
        }

        throw new UnsupportedPasswordHandlerException('Argon2i algorithm is not supported.');
    }
}
