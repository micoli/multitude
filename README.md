# Micoli\Multitude


A collection library for PHP.

[![Build Status](https://github.com/micoli/Multitude/workflows/Tests/badge.svg)](https://github.com/micoli/Multitude/actions)
[![Coverage Status](https://coveralls.io/repos/github/micoli/Multitude/badge.svg?branch=main)](https://coveralls.io/github/micoli/Multitude?branch=main)
[![Latest Stable Version](http://poser.pugx.org/micoli/multitude/v)](https://packagist.org/packages/micoli/multitude)
[![Total Downloads](http://poser.pugx.org/micoli/multitude/downloads)](https://packagist.org/packages/micoli/multitude)
[![Latest Unstable Version](http://poser.pugx.org/micoli/multitude/v/unstable)](https://packagist.org/packages/micoli/multitude) [![License](http://poser.pugx.org/micoli/multitude/license)](https://packagist.org/packages/micoli/multitude)
[![PHP Version Require](http://poser.pugx.org/micoli/multitude/require/php)](https://packagist.org/packages/micoli/multitude)

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
- `new MutableSet(['a','b','c'])`
- `new MutableMap([2=>'a',3=>'b',4=>'c'])`
- `new MutableMap([[2,'a'],['2','aa'],[3,'b'],['3','bb'],[4,'c'])`

You can use fromTuples constructor if you need a strong typing for keys of your map, e.g. `'2'` key is different of `2`.

Methods that accept a `bool $throw` parameter will trigger an exception if `$throw == true` or fails silently if `$throw == false`.

## Example with an associative array
[//]: <> (class-method-code-placeholder-start "Micoli\Multitude\Tests\IntegrationTest::testItShouldFullyWorkWithAssociativeArray" "")
```php
    public function testItShouldFullyWorkWithAssociativeArray(): void
    {
        /** @var ImmutableMap<string, array{value:int,tags:list<string>}> $map */
        $map = new ImmutableMap([
            ['library', ['value' => 10, 'tags' => ['tag1']]],
            ['projects', ['value' => 5, 'tags' => ['tag2']]],
            ['gist', ['value' => 7, 'tags' => ['tag1', 'tag2']]],
            ['repository', ['value' => 7, 'tags' => ['tag3']]],
        ]);
        $totalSum = $map
            ->filter(fn (array $project, mixed $category): bool => array_search('tag1', $project['tags']) !== false)
            ->reduce(fn (int $sum, mixed $project, mixed $category): int => $sum + $project['value'], 0);
        self::assertSame($totalSum, 17);
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
    /**
     * Add or replace a value in the map
     */
    public function improvedSet(string $newKey, Project $newValue): static
    {
        // do specific stuff, like logging or ther
        return $this->set($newKey, $newValue);
    }
}

```

[//]: <> (include-placeholder-end)

[//]: <> (class-method-code-placeholder-start "Micoli\Multitude\Tests\IntegrationTest::testItShouldFullyWorkWithObjects" "")
```php
    public function testItShouldFullyWorkWithObjects(): void
    {
        $map = new Projects([
            ['library', new Project(10, new Tags(['tag1']))],
            ['projects', new Project(5, new Tags(['tag2']))],
            ['gist', new Project(7, new Tags(['tag1', 'tag2']))],
            ['repository', new Project(7, new Tags(['tag3']))],
        ]);
        $totalSum = $map
            ->filter(fn (Project $project, mixed $category): bool => $project->tags->hasValue('tag1'))
            ->reduce(fn (int $sum, Project $project, mixed $category): int => $sum + $project->value, 0);
        self::assertInstanceOf(
            Projects::class,
            $map->filter(fn (Project $project, mixed $category): bool => true),
        );
        self::assertSame($totalSum, 17);
        self::assertCount(4, $map);
        $newMap = $map->improvedSet('NewType', new Project(10, new Tags(['tag4'])));
        self::assertCount(5, $newMap);
    }
```

[//]: <> (class-method-code-placeholder-end)

# Available verbs

## Verb parity

[//]: <> (classes-methods-comparator-placeholder-start "Micoli\Multitude\Map\ImmutableMap,Micoli\Multitude\Map\MutableMap,Micoli\Multitude\Set\ImmutableSet,Micoli\Multitude\Set\MutableSet" "-")

|  | **ImmutableMap** | **MutableMap** | **ImmutableSet** | **MutableSet** |
|---| -- | -- | -- | -- |
| __construct | [x] | [x] | [x] | [x] |
| append |   |   | [x] | [x] |
| apply | [x] | [x] | [x] | [x] |
| count | [x] | [x] | [x] | [x] |
| filter | [x] | [x] | [x] | [x] |
| first | [x] | [x] | [x] | [x] |
| forEach | [x] | [x] | [x] | [x] |
| fromIterable | [x] | [x] |   |   |
| get | [x] | [x] | [x] | [x] |
| getIterator | [x] | [x] | [x] | [x] |
| getTuples | [x] | [x] |   |   |
| hasIndex |   |   | [x] | [x] |
| hasKey | [x] | [x] |   |   |
| hasValue | [x] | [x] | [x] | [x] |
| indexDiff |   |   | [x] | [x] |
| indexIntersect |   |   | [x] | [x] |
| isEmpty | [x] | [x] | [x] | [x] |
| keyDiff | [x] | [x] |   |   |
| keyIntersect | [x] | [x] |   |   |
| keys | [x] | [x] | [x] | [x] |
|  | **ImmutableMap** | **MutableMap** | **ImmutableSet** | **MutableSet** |
| last | [x] | [x] | [x] | [x] |
| map | [x] | [x] | [x] | [x] |
| offsetExists | [x] | [x] |   |   |
| offsetGet | [x] | [x] |   |   |
| offsetSet | [x] | [x] |   |   |
| offsetUnset | [x] | [x] |   |   |
| reduce | [x] | [x] | [x] | [x] |
| remove |   |   | [x] | [x] |
| removeKey | [x] | [x] |   |   |
| removeValue | [x] | [x] |   |   |
| set | [x] | [x] |   |   |
| slice | [x] | [x] | [x] | [x] |
| sort | [x] | [x] | [x] | [x] |
| toArray | [x] | [x] | [x] | [x] |
| toImmutable |   | [x] |   | [x] |
| toMutable | [x] |   | [x] |   |
| valueDiff | [x] | [x] | [x] | [x] |
| valueIntersect | [x] | [x] | [x] | [x] |
| values | [x] | [x] | [x] | [x] |



[//]: <> (classes-methods-comparator-placeholder-end)


## AbstractSet

[//]: <> (class-method-summary-placeholder-start "Micoli\Multitude\Set\AbstractSet" " - ")

 -  [__construct](#user-content-AbstractSet____construct)
 -  [append](#user-content-AbstractSet__append)
 -  [apply](#user-content-AbstractSet__apply)
 -  [count](#user-content-AbstractSet__count)
 -  [filter](#user-content-AbstractSet__filter)
 -  [first](#user-content-AbstractSet__first)
 -  [forEach](#user-content-AbstractSet__forEach)
 -  [get](#user-content-AbstractSet__get)
 -  [getIterator](#user-content-AbstractSet__getIterator)
 -  [hasIndex](#user-content-AbstractSet__hasIndex)
 -  [hasValue](#user-content-AbstractSet__hasValue)
 -  [indexDiff](#user-content-AbstractSet__indexDiff)
 -  [indexIntersect](#user-content-AbstractSet__indexIntersect)
 -  [isEmpty](#user-content-AbstractSet__isEmpty)
 -  [keys](#user-content-AbstractSet__keys)
 -  [last](#user-content-AbstractSet__last)
 -  [map](#user-content-AbstractSet__map)
 -  [reduce](#user-content-AbstractSet__reduce)
 -  [remove](#user-content-AbstractSet__remove)
 -  [slice](#user-content-AbstractSet__slice)
 -  [sort](#user-content-AbstractSet__sort)
 -  [toArray](#user-content-AbstractSet__toArray)
 -  [valueDiff](#user-content-AbstractSet__valueDiff)
 -  [valueIntersect](#user-content-AbstractSet__valueIntersect)
 -  [values](#user-content-AbstractSet__values)

[//]: <> (class-method-summary-placeholder-end)

## AbstractMap

[//]: <> (class-method-summary-placeholder-start "Micoli\Multitude\Map\AbstractMap" " - ")

 -  [__construct](#user-content-AbstractMap____construct)
 -  [apply](#user-content-AbstractMap__apply)
 -  [count](#user-content-AbstractMap__count)
 -  [filter](#user-content-AbstractMap__filter)
 -  [first](#user-content-AbstractMap__first)
 -  [forEach](#user-content-AbstractMap__forEach)
 -  [fromIterable](#user-content-AbstractMap__fromIterable)
 -  [get](#user-content-AbstractMap__get)
 -  [getIterator](#user-content-AbstractMap__getIterator)
 -  [getTuples](#user-content-AbstractMap__getTuples)
 -  [hasKey](#user-content-AbstractMap__hasKey)
 -  [hasValue](#user-content-AbstractMap__hasValue)
 -  [isEmpty](#user-content-AbstractMap__isEmpty)
 -  [keyDiff](#user-content-AbstractMap__keyDiff)
 -  [keyIntersect](#user-content-AbstractMap__keyIntersect)
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
 -  [sort](#user-content-AbstractMap__sort)
 -  [toArray](#user-content-AbstractMap__toArray)
 -  [valueDiff](#user-content-AbstractMap__valueDiff)
 -  [valueIntersect](#user-content-AbstractMap__valueIntersect)
 -  [values](#user-content-AbstractMap__values)

[//]: <> (class-method-summary-placeholder-end)

## AbstractSet
[//]: <> (class-method-documentation-placeholder-start "Micoli\Multitude\Set\AbstractSet" "### ")

### `AbstractSet::__construct` <a id="AbstractSet____construct"></a>

`public function __construct(iterable $values = [])`


### `AbstractSet::append` <a id="AbstractSet__append"></a>

`public function append(mixed $newValue, bool $throw = true): static`

Append a value at the end of the set
### `AbstractSet::apply` <a id="AbstractSet__apply"></a>

`public function apply(callable $callable): static`

Replace all values by applying a callback to the current instance
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
### `AbstractSet::get` <a id="AbstractSet__get"></a>

`public function get(int $index, mixed $defaultValue = null): mixed`

Return a value in the set by index
### `AbstractSet::getIterator` <a id="AbstractSet__getIterator"></a>

`public function getIterator(): Traversable`

Return an iterator for values
### `AbstractSet::hasIndex` <a id="AbstractSet__hasIndex"></a>

`public function hasIndex(int $index): bool`

Return if a set contains an index
### `AbstractSet::hasValue` <a id="AbstractSet__hasValue"></a>

`public function hasValue(mixed $searchedValue): bool`

Return if a set contains a value
### `AbstractSet::indexDiff` <a id="AbstractSet__indexDiff"></a>

`public function indexDiff(AbstractSet $compared): static`

Return a set of all items where keys are not in argument set
### `AbstractSet::indexIntersect` <a id="AbstractSet__indexIntersect"></a>

`public function indexIntersect(AbstractSet $compared): static`

Return a map of all items where keys are in arguments map
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

`public function map(callable $callable)`

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
### `AbstractSet::sort` <a id="AbstractSet__sort"></a>

`public function sort(callable $callable): static`

Sort the map using a callback function

callback is of callable (TValue, TValue, int, int): int

and must return -1,0,1 as spaceship operator
### `AbstractSet::toArray` <a id="AbstractSet__toArray"></a>

`public function toArray(): array`

Return an array representing the values
### `AbstractSet::valueDiff` <a id="AbstractSet__valueDiff"></a>

`public function valueDiff(AbstractSet $compared): static`

Return a Set of all items where values are not in argument set
### `AbstractSet::valueIntersect` <a id="AbstractSet__valueIntersect"></a>

`public function valueIntersect(AbstractSet $compared): static`

Return a set of all items where values are in argument set
### `AbstractSet::values` <a id="AbstractSet__values"></a>

`public function values(): Generator`

Return an iterator of values

[//]: <> (class-method-documentation-placeholder-end)

## AbstractMap

[//]: <> (class-method-documentation-placeholder-start "Micoli\Multitude\Map\AbstractMap" "### ")

### `AbstractMap::__construct` <a id="AbstractMap____construct"></a>

`public function __construct(array $tuples = [])`


### `AbstractMap::apply` <a id="AbstractMap__apply"></a>

`public function apply(callable $callable): static`

Replace all values by applying a callback to the current instance
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
### `AbstractMap::fromIterable` <a id="AbstractMap__fromIterable"></a>

`public static function fromIterable(iterable $values): static`

Return a new instance from an array.
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
### `AbstractMap::hasValue` <a id="AbstractMap__hasValue"></a>

`public function hasValue(mixed $searchedValue): bool`

Return if a map contains a specific value
### `AbstractMap::isEmpty` <a id="AbstractMap__isEmpty"></a>

`public function isEmpty(): bool`

Return if a map is empty
### `AbstractMap::keyDiff` <a id="AbstractMap__keyDiff"></a>

`public function keyDiff(AbstractMap $compared): static`

Return a map of all items where keys are not in argument map
### `AbstractMap::keyIntersect` <a id="AbstractMap__keyIntersect"></a>

`public function keyIntersect(AbstractMap $compared): static`

Return a map of all items where keys are in arguments map
### `AbstractMap::keys` <a id="AbstractMap__keys"></a>

`public function keys(): Generator`

Return an iterator of keys
### `AbstractMap::last` <a id="AbstractMap__last"></a>

`public function last(bool $throw = true): mixed`

Return the latest value in the map
### `AbstractMap::map` <a id="AbstractMap__map"></a>

`public function map(callable $callable)`

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
### `AbstractMap::sort` <a id="AbstractMap__sort"></a>

`public function sort(callable $callable): static`

Sort the map using a callback function

callback is of callable(TValue, TValue, TKey, TKey, int, int): int

and must return -1,0,1 as spaceship operator
### `AbstractMap::toArray` <a id="AbstractMap__toArray"></a>

`public function toArray(): array`

Return an array representing the values
### `AbstractMap::valueDiff` <a id="AbstractMap__valueDiff"></a>

`public function valueDiff(AbstractMap $compared): static`

Return a map of all items where values are not in arguments map
### `AbstractMap::valueIntersect` <a id="AbstractMap__valueIntersect"></a>

`public function valueIntersect(AbstractMap $compared): static`

Return a map of all items where values are in argument map
### `AbstractMap::values` <a id="AbstractMap__values"></a>

`public function values(): Generator`

Return an iterator of values

[//]: <> (class-method-documentation-placeholder-end)
