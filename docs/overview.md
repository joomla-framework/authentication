# Using the Authentication Package

The authentication package provides a decoupled authentication system for providing authentication in your
application.  Authentication strategies are swappable.

Authentication would generally be performed in your application by doing the following:

```php
$credentialStore = array(
	'user1' => '$2y$12$QjSH496pcT5CEbzjD/vtVeH03tfHKFy36d4J0Ltp3lRtee9HDxY3K',
	'user2' => '$2y$12$QjSH496pcT5CEbzjD/vtVeH03tfHKFy36d4J0Ltp3lsdgnasdfasd'
)

$authentication = new Authentication;
$authentication->addStrategy('local', new LocalStrategy($input, $credentialStore));

// Username and password are retrieved using $input->get('username') and $input->get('password') respectively.
$username = $authentication->authenticate();                     // Try all strategies

$username = $authentication->authenticate(array('local'));       // Just use the 'local' strategy
```



## class `Authentication\Authentication`

### <> public addStrategy(String $strategyName, AuthenticationStrategyInterface $strategy)

Adds a strategy object to the list of available strategies to use for authentication.


### <> public authenticate($strategies = array())

Attempts authentication.  Uses all strategies if the array is empty, or a subset of one or more if provided.


### <> public getResults()

Gets a hashed array of strategies and authentication results.

```php
$authentication->getResults();

// Might return
array(
	'local' => Authentication::SUCCESS,
	'strategy2' => Authentication::MISSING_CREDENTIALS
)
```


## class `Authentication\Stragies\LocalStrategy`

Provides authentication support for local credentials obtained from a ```Joomla\Input\Input``` object.

```php
$credentialStore = array(
	'jimbob' => 'agdj4345235',		// username and password hash
	'joe' => 'sdgjrly435235'		// username and password hash
)

$strategy = new Authentication\Strategies\LocalStrategy($input, $credentialStore);
```


## class `Authentication\Stragies\DatabaseStrategy`

Provides authentication support for user credentials stored in a ```Joomla\Database\DatabaseDriver``` object
and obtained via a ```Joomla\Input\Input``` object.  The database details can be configured via an options array:

```php
use Joomla\Database\DatabaseDriver;

$options = array(
	'database_table'  => '#__users', // Name of the database table the user data is stored in
	'username_column' => 'username', // Name of the column in the database containing usernames
	'password_column' => 'password', // Name of the column in the database containing passwords
)

$database = DatabaseDriver::getInstance();

$strategy = new Authentication\Strategies\Database($input, $database, $options);
```


## interface `Authentication\AuthenticationStrategyInterface`

### <> public authenticate()

This function must perform whatever actions are necessary to verify whether there are valid credentials.  The
credential source is generally determined by the object constructor where they get passed in as dependencies.
As an example, LocalStrategy takes an Input object and a hash of credential pairs.  The method should set the
 status of the authentication attempt for retrieval from the getStatus() method.


### <> public getStatus()

This function should return the status of the last authentication attempt (specified using Authentication class
constants).

## Things to know before you build on this

**A failed login tells the caller which half was wrong.** `getResults()` distinguishes
`Authentication::NO_SUCH_USER` from `Authentication::INVALID_CREDENTIALS`. Log the distinction,
but show the user one message — otherwise the endpoint confirms which usernames exist.

**An unknown user is rejected faster than a wrong password.** When `getHashedPassword()` returns
`false`, the strategy returns before any hash is verified, so no bcrypt or Argon2 work happens.
The difference is measurable over the network and enumerates usernames even when the status is
hidden. Verify against a dummy hash before rejecting:

```php
protected function doAuthenticate($username, $password)
{
    $hash = $this->getHashedPassword($username) ?: self::DUMMY_HASH;

    $valid = $this->verifyPassword($username, $password, $hash);
    // … then decide, using the same code path either way
}
```

**There is no brute-force protection.** No attempt counter, no lockout, no delay. Add rate
limiting around `authenticate()`.

**Hashes are never upgraded.** `HandlerInterface` has no `needsRehash()`, so a password hashed
years ago keeps its original cost even after you raise it. A successful login is the only moment
the plaintext is available — do the rehash there yourself:

```php
if ($username !== false && password_needs_rehash($storedHash, PASSWORD_BCRYPT, ['cost' => 12])) {
    $users->updatePassword($username, $handler->hashPassword($password, ['cost' => 12]));
}
```

**`Argon2iHandler` produces Argon2id in the fallback path.** When `PASSWORD_ARGON2I` is undefined
it calls `sodium_crypto_pwhash_str()`, which emits an `$argon2id$` hash. The class name then does
not describe the output. It also ignores `$options` on that path and always uses the INTERACTIVE
limits.

**A malformed hash crashes the login.** `Argon2iHandler::validatePassword()` calls
`sodium_crypto_pwhash_str_verify()` without a `try`/`catch`, so a truncated or foreign hash in the
database raises a `SodiumException` instead of failing the attempt.

**`DatabaseStrategy` ignores account state.** The query selects the password column only, so
blocked or unactivated accounts authenticate like any other. Check that after `authenticate()`
returns.

**`LocalStrategy` is for tests.** It holds the credential map in memory as plain values.
