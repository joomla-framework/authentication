<?php

/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication;

use Joomla\Authentication\Password\BCryptHandler;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\Password\RehashAwareHandlerInterface;

/**
 * Abstract AuthenticationStrategy for username/password based authentication
 *
 * @since  1.1.0
 */
abstract class AbstractUsernamePasswordAuthenticationStrategy implements AuthenticationStrategyInterface
{
    /**
     * The password handler to validate the password against.
     *
     * @var    HandlerInterface
     * @since  1.2.0
     */
    protected $passwordHandler;

    /**
     * The last authentication status.
     *
     * @var    integer
     * @since  1.1.0
     */
    protected $status;

    /**
     * Constructor.
     *
     * @param   ?HandlerInterface  $passwordHandler  The password handler.
     *
     * @since   1.2.0
     */
    public function __construct(?HandlerInterface $passwordHandler = null)
    {
        $this->passwordHandler = $passwordHandler ?: new BCryptHandler();
    }

    /**
     * Attempt to authenticate the username and password pair.
     *
     * @param   string  $username  The username to authenticate.
     * @param   string  $password  The password to attempt authentication with.
     *
     * @return  string|boolean  A string containing a username if authentication is successful, false otherwise.
     *
     * @since   1.1.0
     */
    protected function doAuthenticate($username, $password)
    {
        $hashedPassword = $this->getHashedPassword($username);

        if ($hashedPassword === false) {
            $this->status = Authentication::NO_SUCH_USER;

            return false;
        }

        if (!$this->verifyPassword($username, $password, $hashedPassword)) {
            $this->status = Authentication::INVALID_CREDENTIALS;

            return false;
        }

        if (
            $this->passwordHandler instanceof RehashAwareHandlerInterface
            && $this->passwordHandler->needsRehash($hashedPassword)
        ) {
            $this->rehashPassword($username, $password);
        }

        $this->status = Authentication::SUCCESS;

        return $username;
    }

    /**
     * Retrieve the hashed password for the specified user.
     *
     * @param   string  $username  Username to lookup.
     *
     * @return  string|boolean  Hashed password on success or boolean false on failure.
     *
     * @since   1.1.0
     */
    abstract protected function getHashedPassword($username);

    /**
     * Get the status of the last authentication attempt.
     *
     * @return  integer  Authentication class constant result.
     *
     * @since   1.1.0
     */
    public function getResult()
    {
        return $this->status;
    }

    /**
     * Store a new hash of the password after a successful login with an outdated hash.
     *
     * Called when the password handler implements RehashAwareHandlerInterface and reports that the stored hash was
     * not created with its current algorithm and options. The default implementation does nothing; override it to
     * replace the stored hash with `$this->passwordHandler->hashPassword($password)`.
     *
     * @param   string  $username  The username that was authenticated.
     * @param   string  $password  The plaintext password that was verified.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function rehashPassword(string $username, string $password): void
    {
    }

    /**
     * Attempt to verify the username and password pair.
     *
     * @param   string  $username        The username to authenticate.
     * @param   string  $password        The password to attempt authentication with.
     * @param   string  $hashedPassword  The hashed password to attempt authentication against.
     *
     * @return  boolean
     *
     * @since   1.1.0
     */
    protected function verifyPassword($username, $password, $hashedPassword)
    {
        return $this->passwordHandler->validatePassword($password, $hashedPassword);
    }
}
