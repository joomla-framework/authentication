# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement and simplifies `Argon2iHandler` now that the old libsodium
extensions are out of scope.

## At a glance

| | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| Method signatures | — | unchanged |
| PECL libsodium 1.x support | present | **removed** |
| `UnsupportedPasswordHandlerException` from `Argon2iHandler` | thrown when unsupported | **no longer thrown** |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## `Argon2iHandler` dropped its fallbacks

In 3.x `hashPassword()` tried three routes in order — the native `password_hash()` with
`PASSWORD_ARGON2I`, then `sodium_crypto_pwhash_str()`, then the PECL libsodium 1.x functions in the
`\Sodium\` namespace — and threw `UnsupportedPasswordHandlerException` if none was available.

4.0.0 keeps the first two and removes both the `\Sodium\` branch and the throw:

```php
// v4
if (\defined('PASSWORD_ARGON2I')) {
    return password_hash($plaintext, \PASSWORD_ARGON2I, $options);
}

$hash = sodium_crypto_pwhash_str(
    $plaintext,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
);
```

Two consequences:

* **PECL libsodium 1.x is no longer supported.** It was superseded by the bundled `sodium`
  extension in PHP 7.2, so on PHP 8.3 this branch was unreachable anyway.
* **The handler no longer throws `UnsupportedPasswordHandlerException`.** On a build without the
  sodium extension, `hashPassword()` now fails with `Error: Call to undefined function
  sodium_crypto_pwhash_str()` instead. Check support before using the handler:

```php
if (!Argon2iHandler::isSupported()) {
    // pick another handler
}
```

`Argon2idHandler` is unchanged and still throws `UnsupportedPasswordHandlerException` when
`PASSWORD_ARGON2ID` is not defined.

## No signature changes

`HandlerInterface`, `BCryptHandler`, `Argon2idHandler`, `Authentication`, both strategies and
`AuthenticationStrategyInterface` are unchanged.

## Dependency changes

| Package | v3 (3.0.2) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |

The optional packages in `suggest` moved to `^4.0`: `joomla/database`, `joomla/input`.
