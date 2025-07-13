## Updating from v3 to v4

The following changes were made to the Authentication package between v3 and v4.

### Minimum supported PHP version raised

All Framework packages now require PHP 8.3 or newer.

### Support for PECL 1 libsodium crypto package removed

Since PHP 8.3 does not support the libsodium package anymore, the corresponding code was removed from the password handlers.
