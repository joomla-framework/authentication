# Updating from v4 to v5

Release 5.0 adds native parameter and return types to the whole package and moves `needsRehash()`
into `HandlerInterface`.

## At a glance

| | v4 | v5 |
|---|---|---|
| Native types on public and protected methods | no | **yes** |
| `HandlerInterface::needsRehash()` | in `RehashAwareHandlerInterface` | **required** |
| `RehashAwareHandlerInterface` | optional extension | deprecated, empty |
| `getResult()` before the first attempt | `null` (documented as `int`) | `?int` |

## Native types

Every method now declares its parameter and return types. Classes that implement the interfaces or
extend the bundled classes must declare compatible signatures:

| Method | v5 signature |
|---|---|
| `HandlerInterface::hashPassword()` | `hashPassword(string $plaintext, array $options = []): string` |
| `HandlerInterface::validatePassword()` | `validatePassword(string $plaintext, string $hashed): bool` |
| `HandlerInterface::isSupported()` | `static isSupported(): bool` |
| `AuthenticationStrategyInterface::authenticate()` | `authenticate(): string\|false` |
| `AuthenticationStrategyInterface::getResult()` | `getResult(): ?int` |
| `AbstractUsernamePasswordAuthenticationStrategy::doAuthenticate()` | `doAuthenticate(string $username, string $password): string\|false` |
| `AbstractUsernamePasswordAuthenticationStrategy::getHashedPassword()` | `getHashedPassword(string $username): string\|false` |
| `AbstractUsernamePasswordAuthenticationStrategy::verifyPassword()` | `verifyPassword(string $username, string $password, string $hashedPassword): bool` |
| `Authentication::addStrategy()` | `addStrategy(string $strategyName, AuthenticationStrategyInterface $strategy): void` |
| `Authentication::authenticate()` | `authenticate(array $strategies = []): string\|false` |
| `Authentication::getResults()` | `getResults(): array` |

A custom strategy written for v4:

```php
// v4
protected function getHashedPassword($username)
{
    return $this->store[$username] ?? false;
}

// v5
protected function getHashedPassword(string $username): string|false
{
    return $this->store[$username] ?? false;
}
```

The properties `AbstractUsernamePasswordAuthenticationStrategy::$passwordHandler`
(`HandlerInterface`) and `$status` (`?int`, initialised to `null`) are typed as well; a subclass that
redeclares them must use the same types.

## `needsRehash()` is part of `HandlerInterface`

4.x added `needsRehash(string $hash, array $options = []): bool` through the optional
`RehashAwareHandlerInterface`. In 5.0 every handler must implement it, and
`AbstractUsernamePasswordAuthenticationStrategy::doAuthenticate()` calls it on every successful login
to decide whether to call the `rehashPassword()` hook. A custom handler built on PHP's password API
implements it with `password_needs_rehash()`:

```php
public function needsRehash(string $hash, array $options = []): bool
{
    return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
}
```

`RehashAwareHandlerInterface` still exists so that classes naming it keep working, but it no longer
declares anything of its own. It is deprecated and will be removed in 6.0; implement
`HandlerInterface` instead.
