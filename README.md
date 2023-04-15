# Micoli\Multitude


A collection library for PHP.

[![Build Status](https://github.com/micoli/Multitude/workflows/Tests/badge.svg)](https://github.com/micoli/Multitude/actions)
[![Coverage Status](https://coveralls.io/repos/github/micoli/Multitude/badge.svg?branch=main)](https://coveralls.io/github/micoli/Multitude?branch=main)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

Two types of collections are available:
- Sets (`MutableSet` and `ImmutableSet`), are sequences of unique values. Values can be of any types.
- Map (`MutableMap` and `ImmutableMap`), is a sequential collection of key-value pairs. Keys can be any type, but must be unique. Values can be of any types.
  
In both `ImmutableSet` and `ImmutableMap`, if a method alter the content af the inner values, a new instance is returned of the same type. Oppositely, in `MutableSet` and `MutableMap`, same methods are altering the inner values.

Methods are the most possible fluent.

Thanks to https://github.com/BenMorel for the main ideas used in that library, this is a complete rewrite of it's initial version.

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require micoli/multitude
```

## Requirements

This library requires PHP 8.0 or later.

## Project status

While this library is still under development, it is still in early development status. It follows semver version tagging.

## Quick start

Constructor are only statics:
- `MutableSet::fromArray(['a','b','c'])`
- `MutableMap::fromArray([2=>'a',3=>'b',4=>'c'])`
- `MutableMap::fromTuples([[2,'a'],['2','aa'],[3,'b'],['3','bb'],[4,'c'])`

You can use fromTuples constructor if you need a strong typing for keys of your map, e.g. `'2'` key is different of `2`.

Methods that accept a `bool $throw` parameter will trigger an exception if `$throw == true` or fails silently if `$throw == false`.

## Example with an associative array
[//]: <> (class-method-code-placeholder-start "Micoli\Multitude\Tests\IntegrationTest::testItShouldFullyWorkWithAssociativeArray" "")
```php
    public function testItShouldFullyWorkWithAssociativeArray(): void
    {
        /** @var ImmutableMap<string, array{value:int,tags:list<string>}> $map */
        $map = ImmutableMap::fromTuples([
            ['library', ['value' => 10, 'tags' => ['tag1']]],
            ['projects', ['value' => 5, 'tags' => ['tag2']]],
            ['gist', ['value' => 7, 'tags' => ['tag1', 'tag2']]],
            ['repository', ['value' => 7, 'tags' => ['tag3']]],
        ]);
        $sum = $map
            ->filter(fn (array $project, mixed $category): bool => array_search('tag1', $project['tags']) !== false)
            ->reduce(function (int $sum, mixed $project, mixed $category): int {
                $sum += $project['value'];

                return $sum;
            }, 0);
        self::assertSame($sum, 17);
        self::assertCount(4, $map);
    }
```

[//]: <> (class-method-code-placeholder-end)

## Example with an immutable map fully typed

[//]: <> (include-placeholder-start "./tests/fixtures/Project.php" "Project.php")

File: `Project.php`
```php
<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

class Project
{
    public function __construct(
        public readonly int $value,
        public readonly Tags $tags,
    ) {
    }
}

```

[//]: <> (include-placeholder-end)

[//]: <> (include-placeholder-start "./tests/fixtures/Tags.php" "Tags")

File: `Tags`
```php
<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

use Micoli\Multitude\Set\ImmutableSet;

/**
 * @extends ImmutableSet<string>
 */
class Tags extends ImmutableSet
{
}

```

[//]: <> (include-placeholder-end)

[//]: <> (include-placeholder-start "./tests/fixtures/Projects.php" "Projects")

File: `Projects`
```php
<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests\Fixtures;

use Micoli\Multitude\Map\ImmutableMap;

/**
 * @extends ImmutableMap<string, Project>
 */
class Projects extends ImmutableMap
{
}

```

[//]: <> (include-placeholder-end)

[//]: <> (class-method-code-placeholder-start "Micoli\Multitude\Tests\IntegrationTest::testItShouldFullyWorkWithObjects" "")
```php
    public function testItShouldFullyWorkWithObjects(): void
    {
        $map = Projects::fromTuples([
            ['library', new Project(10, Tags::fromArray(['tag1']))],
            ['projects', new Project(5, Tags::fromArray(['tag2']))],
            ['gist', new Project(7, Tags::fromArray(['tag1', 'tag2']))],
            ['repository', new Project(7, Tags::fromArray(['tag3']))],
        ]);
        $sum = $map
            ->filter(fn (Project $project, mixed $category): bool => $project->tags->hasValue('tag1'))
            ->reduce(function (int $sum, Project $project, mixed $category): int {
                $sum += $project->value;

                return $sum;
            }, 0);
        self::assertSame($sum, 17);
        self::assertCount(4, $map);
    }
```

[//]: <> (class-method-code-placeholder-end)

# Available verbs

## AbstractSet

[//]: <> (class-method-summary-placeholder-start "Micoli\Multitude\Set\AbstractSet" " - ")

 -  [append](#user-content-AbstractSet__append)
 -  [count](#user-content-AbstractSet__count)
 -  [filter](#user-content-AbstractSet__filter)
 -  [first](#user-content-AbstractSet__first)
 -  [forEach](#user-content-AbstractSet__forEach)
 -  [fromArray](#user-content-AbstractSet__fromArray)
 -  [get](#user-content-AbstractSet__get)
 -  [getIterator](#user-content-AbstractSet__getIterator)
 -  [hasValue](#user-content-AbstractSet__hasValue)
 -  [isEmpty](#user-content-AbstractSet__isEmpty)
 -  [keys](#user-content-AbstractSet__keys)
 -  [last](#user-content-AbstractSet__last)
 -  [map](#user-content-AbstractSet__map)
 -  [reduce](#user-content-AbstractSet__reduce)
 -  [remove](#user-content-AbstractSet__remove)
 -  [slice](#user-content-AbstractSet__slice)
 -  [toArray](#user-content-AbstractSet__toArray)
 -  [values](#user-content-AbstractSet__values)

[//]: <> (class-method-summary-placeholder-end)

## AbstractMap

[//]: <> (class-method-summary-placeholder-start "Micoli\Multitude\Map\AbstractMap" " - ")

 -  [count](#user-content-AbstractMap__count)
 -  [filter](#user-content-AbstractMap__filter)
 -  [first](#user-content-AbstractMap__first)
 -  [forEach](#user-content-AbstractMap__forEach)
 -  [fromArray](#user-content-AbstractMap__fromArray)
 -  [fromTuples](#user-content-AbstractMap__fromTuples)
 -  [get](#user-content-AbstractMap__get)
 -  [getIterator](#user-content-AbstractMap__getIterator)
 -  [getTuples](#user-content-AbstractMap__getTuples)
 -  [hasKey](#user-content-AbstractMap__hasKey)
 -  [isEmpty](#user-content-AbstractMap__isEmpty)
 -  [keys](#user-content-AbstractMap__keys)
 -  [last](#user-content-AbstractMap__last)
 -  [map](#user-content-AbstractMap__map)
 -  [offsetExists](#user-content-AbstractMap__offsetExists)
 -  [offsetGet](#user-content-AbstractMap__offsetGet)
 -  [offsetSet](#user-content-AbstractMap__offsetSet)
 -  [offsetUnset](#user-content-AbstractMap__offsetUnset)
 -  [reduce](#user-content-AbstractMap__reduce)
 -  [removeKey](#user-content-AbstractMap__removeKey)
 -  [removeValue](#user-content-AbstractMap__removeValue)
 -  [set](#user-content-AbstractMap__set)
 -  [slice](#user-content-AbstractMap__slice)
 -  [toArray](#user-content-AbstractMap__toArray)
 -  [values](#user-content-AbstractMap__values)

[//]: <> (class-method-summary-placeholder-end)

## AbstractSet
[//]: <> (class-method-documentation-placeholder-start "Micoli\Multitude\Set\AbstractSet" "### ")

### `AbstractSet::append` <a id="AbstractSet__append"></a>

`public function append(mixed $newValue, bool $throw = true): static`

Append a value at the end of the set
### `AbstractSet::count` <a id="AbstractSet__count"></a>

`public function count(): int`

return the number of items in the set
### `AbstractSet::filter` <a id="AbstractSet__filter"></a>

`public function filter(callable $callable): static`

Filter the set using a callback function
### `AbstractSet::first` <a id="AbstractSet__first"></a>

`public function first(bool $throw = true): mixed`

Return the first value in the set
### `AbstractSet::forEach` <a id="AbstractSet__forEach"></a>

`public function forEach(callable $callable): static`

Apply a callback on set values

Callback receive `$value` and `$index`
### `AbstractSet::fromArray` <a id="AbstractSet__fromArray"></a>

`public static function fromArray(iterable $values): static`

Return a new instance from an array. dedup values on construction
### `AbstractSet::get` <a id="AbstractSet__get"></a>

`public function get(mixed $index, mixed $defaultValue = null): mixed`

Return a value in the set by index
### `AbstractSet::getIterator` <a id="AbstractSet__getIterator"></a>

`public function getIterator(): Traversable`

Return an iterator for values
### `AbstractSet::hasValue` <a id="AbstractSet__hasValue"></a>

`public function hasValue(mixed $searchedValue): bool`

Return if a set contains a value
### `AbstractSet::isEmpty` <a id="AbstractSet__isEmpty"></a>

`public function isEmpty(): bool`

Return if a set is empty
### `AbstractSet::keys` <a id="AbstractSet__keys"></a>

`public function keys(): Generator`

Return an iterator of keys
### `AbstractSet::last` <a id="AbstractSet__last"></a>

`public function last(bool $throw = true): mixed`

Return the latest value in the set
### `AbstractSet::map` <a id="AbstractSet__map"></a>

`public function map(callable $callable): static`

Applies the callback to the values, keys are preserved

Callback receive `$value` and `$index`
### `AbstractSet::reduce` <a id="AbstractSet__reduce"></a>

`public function reduce(callable $callable, mixed $accumulator): mixed`

Iteratively reduce the Set to a single value using a callback function

Callback receive `$accumulator`,`$value` and `$index`
### `AbstractSet::remove` <a id="AbstractSet__remove"></a>

`public function remove(mixed $searchedValue, bool $throw = true): static`

Remove a value in the set
### `AbstractSet::slice` <a id="AbstractSet__slice"></a>

`public function slice(int $offset, ?int $length = null): static`

Extract a slice of the set
### `AbstractSet::toArray` <a id="AbstractSet__toArray"></a>

`public function toArray(): array`

Return an array representing the values
### `AbstractSet::values` <a id="AbstractSet__values"></a>

`public function values(): Generator`

Return an iterator of values

[//]: <> (class-method-documentation-placeholder-end)

## AbstractMap

[//]: <> (class-method-documentation-placeholder-start "Micoli\Multitude\Map\AbstractMap" "### ")

### `AbstractMap::count` <a id="AbstractMap__count"></a>

`public function count(): int`

Return the number of items in the map
### `AbstractMap::filter` <a id="AbstractMap__filter"></a>

`public function filter(callable $callable): static`

Filter the map using a callback function
### `AbstractMap::first` <a id="AbstractMap__first"></a>

`public function first(bool $throw = true): mixed`

Return the first value in the map
### `AbstractMap::forEach` <a id="AbstractMap__forEach"></a>

`public function forEach(callable $callable): static`

Apply a callback on set values
### `AbstractMap::fromArray` <a id="AbstractMap__fromArray"></a>

`public static function fromArray(iterable $values): static`

Return a new instance from an array.
### `AbstractMap::fromTuples` <a id="AbstractMap__fromTuples"></a>

`public static function fromTuples(iterable $values): static`

Return a new instance from an array of [$key,$value]. $keys types are preserved
### `AbstractMap::get` <a id="AbstractMap__get"></a>

`public function get(mixed $searchedKey, mixed $defaultValue = null): mixed`

Return a value in the map by index
### `AbstractMap::getIterator` <a id="AbstractMap__getIterator"></a>

`public function getIterator(): Traversable`

Return an iterator for values by keys
### `AbstractMap::getTuples` <a id="AbstractMap__getTuples"></a>

`public function getTuples(): array`


### `AbstractMap::hasKey` <a id="AbstractMap__hasKey"></a>

`public function hasKey(mixed $searchedKey): bool`

Return if a map contains a specific key
### `AbstractMap::isEmpty` <a id="AbstractMap__isEmpty"></a>

`public function isEmpty(): bool`

Return if a map is empty
### `AbstractMap::keys` <a id="AbstractMap__keys"></a>

`public function keys(): Generator`

Return an iterator of keys
### `AbstractMap::last` <a id="AbstractMap__last"></a>

`public function last(bool $throw = true): mixed`

Return the latest value in the map
### `AbstractMap::map` <a id="AbstractMap__map"></a>

`public function map(callable $callable): static`

Applies the callback to the values, keys are preserved
### `AbstractMap::offsetExists` <a id="AbstractMap__offsetExists"></a>

`public function offsetExists(mixed $offset): bool`


### `AbstractMap::offsetGet` <a id="AbstractMap__offsetGet"></a>

`public function offsetGet(mixed $offset): mixed`


### `AbstractMap::offsetSet` <a id="AbstractMap__offsetSet"></a>

`public function offsetSet(mixed $offset, mixed $value): void`


### `AbstractMap::offsetUnset` <a id="AbstractMap__offsetUnset"></a>

`public function offsetUnset(mixed $offset): void`


### `AbstractMap::reduce` <a id="AbstractMap__reduce"></a>

`public function reduce(callable $callable, mixed $accumulator): mixed`

Iteratively reduce the Map to a single value using a callback function

Callback receive `$accumulator`,`$value` and `$key`
### `AbstractMap::removeKey` <a id="AbstractMap__removeKey"></a>

`public function removeKey(mixed $searchedKey): static`

Remove a value in the map by key
### `AbstractMap::removeValue` <a id="AbstractMap__removeValue"></a>

`public function removeValue(mixed $searchedValue): static`

Remove a value in the map by value
### `AbstractMap::set` <a id="AbstractMap__set"></a>

`public function set(mixed $newKey, mixed $newValue): static`

Add or replace a value in the map
### `AbstractMap::slice` <a id="AbstractMap__slice"></a>

`public function slice(int $offset, ?int $length = null): static`

Extract a slice of the map
### `AbstractMap::toArray` <a id="AbstractMap__toArray"></a>

`public function toArray(): array`

Return an array representing the values
### `AbstractMap::values` <a id="AbstractMap__values"></a>

`public function values(): Generator`

Return an iterator of values

[//]: <> (class-method-documentation-placeholder-end)
