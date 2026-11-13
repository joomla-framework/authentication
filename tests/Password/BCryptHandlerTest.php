<?php

/**
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Tests\Password;

use Joomla\Authentication\Password\BCryptHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Joomla\Authentication\Password\BCryptHandler
 */
#[CoversClass(BCryptHandler::class)]
class BCryptHandlerTest extends TestCase
{
    #[TestDox('A password is hashed and validated')]
    public function testAPasswordIsHashedAndValidated()
    {
        $handler = new BCryptHandler();
        $hash    = $handler->hashPassword('password', ['cost' => 4]);
        $this->assertTrue($handler->validatePassword('password', $hash), 'The hashed password was not validated.');
    }

    #[TestDox('A hash with other options or another algorithm needs a rehash')]
    public function testNeedsRehash()
    {
        $handler = new BCryptHandler();
        $hash    = $handler->hashPassword('password', ['cost' => 4]);

        $this->assertFalse($handler->needsRehash($hash, ['cost' => 4]), 'A hash with the same options must not need a rehash.');
        $this->assertTrue($handler->needsRehash($hash, ['cost' => 5]), 'A hash with a lower cost must need a rehash.');
        $this->assertTrue($handler->needsRehash(md5('password')), 'A hash of another algorithm must need a rehash.');
    }

}
