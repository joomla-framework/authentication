<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests\Password;

use Joomla\Authentication\Password\Argon2iHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Authentication\Password\Argon2iHandler
 */
#[CoversClass(Argon2iHandler::class)]
class Argon2iHandlerTest extends TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (!Argon2iHandler::isSupported()) {
            static::markTestSkipped('Argon2i algorithm is not supported.');
        }

        parent::setUpBeforeClass();
    }

    #[TestDox('A password is hashed and validated')]
    public function testAPasswordIsHashedAndValidated()
    {
        $handler = new Argon2iHandler();
        $hash    = $handler->hashPassword('password');
        $this->assertTrue($handler->validatePassword('password', $hash), 'The hashed password was not validated.');
    }

    #[TestDox('A hash with other options or another algorithm needs a rehash')]
    public function testNeedsRehash()
    {
        $handler = new Argon2iHandler();
        $hash    = $handler->hashPassword('password', ['memory_cost' => 1024, 'time_cost' => 1, 'threads' => 1]);

        $this->assertFalse(
            $handler->needsRehash($hash, ['memory_cost' => 1024, 'time_cost' => 1, 'threads' => 1]),
            'A hash with the same options must not need a rehash.'
        );
        $this->assertTrue($handler->needsRehash($hash), 'A hash with weaker options must need a rehash.');
        $this->assertTrue(
            $handler->needsRehash(password_hash('password', \PASSWORD_BCRYPT, ['cost' => 4])),
            'A hash of another algorithm must need a rehash.'
        );
    }

}
