<?php

declare(strict_types=1);

namespace Micoli\Multitude\Tests;

use Micoli\Multitude\Map\ImmutableMap;
use Micoli\Multitude\Tests\Fixtures\Project;
use Micoli\Multitude\Tests\Fixtures\Projects;
use Micoli\Multitude\Tests\Fixtures\Tags;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @psalm-suppress MixedArrayAccess
 * @psalm-suppress MixedAssignment
 * @psalm-suppress MixedInferredReturnType
 * @psalm-suppress MixedOperand
 * @psalm-suppress MixedPropertyFetch
 * @psalm-suppress MixedReturnStatement
 */
class IntegrationTest extends TestCase
{
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
}
