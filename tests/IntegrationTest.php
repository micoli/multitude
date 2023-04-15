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
 */
class IntegrationTest extends TestCase
{
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
}
