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
 * Since 5.0 needsRehash() is part of HandlerInterface, so this interface adds nothing.
 *
 * @since       __DEPLOY_VERSION__
 * @deprecated  6.0 Implement HandlerInterface instead
 */
interface RehashAwareHandlerInterface extends HandlerInterface
{
}
