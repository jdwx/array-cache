# jdwx/array-cache

This is a trivial implementation of PSR CacheInterface using an 
in-memory array.

PSR Cache implementations are frequently heavyweight. They can provide
a ton of backend options that the user may not need or they might be
closely tied to a specific framework that the user doesn't use.

This library is designed to provide a simple, lightweight, and
(nearly) dependency-free PSR cache implementation that can be used as a 
default or placeholder.

For example, this is useful in libraries where a cache is necessary
but the implementation will be chosen by the library's users.  It
can also be used as a development dependency to provide a cache 
implementation for testing.

The cache has a fully-tested implementation of TTLs with microsecond 
precision, so if you set something to expire in one second, it will
expire in one second, not 0-2 seconds.

The cache can be prepopulated with a JSON string or an array of data.
It also supports JSON serialization so you can persist the cache
or inspect its contents if necessary. (Also useful for testing.) 

## Requirements

This library requires PHP 8.0 or later.  psr/simple-cache is the
only runtime dependency.

## Installation

```bash
composer require jdwx/array-cache
```

## Usage

The ArrayCache class implements the PSR-16 [CacheInterface](https://www.php-fig.org/psr/psr-16/).

In addition, it can be preloaded with data:

```php
use JDWX\ArrayCache\ArrayCache;


$cache = new ArrayCache([ 'foo' => 'bar', 'baz' => 'qux' ]);
```

It can be serialized to JSON, which will preserve the TTLs:

```php
$cache = new ArrayCache();
$cache->set( 'foo', 'bar' );
$cache->set( 'baz', 'qux', 5 );
$json = json_encode($cache);
```

It can also be preloaded with JSON:

```php
$st = '{"foo":"bar","baz":"qux"}';
$cache = new ArrayCache($st);
assert( $cache->get('foo') === 'bar' );
assert( $cache->get('baz') === 'qux' );

// or

$cache = new ArrayCache();
$cache->set( 'foo', 'bar' );
$cache->set( 'baz', 'qux', 5 );
$cache->set( 'quux', 'corge' );

$st = json_encode($cache);

$cache = new ArrayCache($st);
assert( $cache->get('foo') === 'bar' );
```

Both importing and exporting JSON will drop expired items.

## Stability

This library is stable and has a full suite of unit tests.  It is considered
suitable for production use within its problem domain. I.e., don't use
this where you should be using something like Redis or 
[symfony/cache](https://symfony.com/components/Cache).

## History

This library was created in 2024 because (the otherwise excellent) 
cache/array-adapter is no longer maintained.
