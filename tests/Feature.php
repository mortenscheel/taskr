<?php

declare(strict_types=1);

use Scheel\Taskr\Config\Color;
use Scheel\Taskr\Config\Config;
use Scheel\Taskr\Context;
use Scheel\Taskr\Taskr;
use Scheel\Taskr\Tasks\Task;
use Scheel\Taskr\Tasks\TaskGroup;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

it('executes tasks', function (): void {
    $executed = false;
    Taskr::make([
        Task::make('Task 1', function () use (&$executed): void {
            $executed = true;
        }),
    ], new NullOutput)->run();
    expect($executed)->toBeTrue();
});

it('doesn\'t execute skipped tasks', function (): void {
    $executed = false;
    Taskr::make([
        Task::make('Skipped task', function () use (&$executed): void {
            $executed = true;
        }, fn (): true => true),
        TaskGroup::make('Skipped group', [
            Task::make('Subtask', function () use (&$executed): void {
                $executed = true;
            }),
        ], fn (): true => true),
    ], new NullOutput)->run();
    expect($executed)->toBeFalse();
});

it('forwards context', function (): void {
    $context = Context::make(['initial' => true]);
    Taskr::make([
        Task::make('Task 1', function (Context $ctx): void {
            if ($ctx->get('initial') === true) {
                $ctx->set('value', 42);
            }
        }),
        Task::make('Task 2', function (Context $ctx): void {
            if ($ctx->get('initial') === true && $ctx->has('value')) {
                $ctx->increment('value');
            }
        }),
    ], new NullOutput)->run($context);
    expect($context->get('value'))->toBe(43);
});

it('fails when incrementing non-integer context', function (): void {
    $context = Context::make(['value' => 'string']);
    $context->increment('value');
})->throws(RuntimeException::class);

it('can increment undefined keys', function (): void {
    $context = Context::make();
    expect($context->get('value'))->toBeNull();
    $context->increment('value');
    expect($context->get('value'))->toBe(1);
});

it('renders output correctly', function (array $tasks, Config $config, string $expected): void {
    $buffer = new BufferedOutput;
    Taskr::make($tasks, $buffer, $config)->run();
    // Replaces ANSI sequences for resetting the output with a simple string.
    $output = preg_replace("/(\x1b\[\d+[A-Z])+/", "RESET\n", $buffer->fetch());
    expect($output)->toBe($expected);
})->with([
    'simple' => fn (): array => [
        [
            Task::make('Task 1', fn (): null => null),
            Task::make('Task 2', fn (): null => null, fn (): true => true),
        ],
        Config::make(),
        <<<'OUTPUT'
⏸ Task 1
⏸ Task 2
RESET
▶ Task 1
⏸ Task 2
RESET
✓ Task 1
⏸ Task 2
RESET
✓ Task 1
⏭ Task 2

OUTPUT,
    ],
    'advanced' => fn (): array => [
        [
            Task::make('A', fn (): null => null),
            TaskGroup::make('B', [
                Task::make('B.1', fn (): null => null),
                TaskGroup::make('B.2', [
                    Task::make('B.2.a', fn (): null => null),
                    Task::make('B.2.b', fn (): null => null),
                ]),
            ]),
        ],
        Config::make(),
        <<<'OUTPUT'
⏸ A
⏸ B
RESET
▶ A
⏸ B
RESET
✓ A
⏸ B
RESET
✓ A
▶ B
  ⏸ B.1
  ⏸ B.2
RESET
✓ A
▶ B
  ▶ B.1
  ⏸ B.2
RESET
✓ A
▶ B
  ✓ B.1
  ⏸ B.2
RESET
✓ A
▶ B
  ✓ B.1
  ▶ B.2
    ⏸ B.2.a
    ⏸ B.2.b
RESET
✓ A
▶ B
  ✓ B.1
  ▶ B.2
    ▶ B.2.a
    ⏸ B.2.b
RESET
✓ A
▶ B
  ✓ B.1
  ▶ B.2
    ✓ B.2.a
    ⏸ B.2.b
RESET
✓ A
▶ B
  ✓ B.1
  ▶ B.2
    ✓ B.2.a
    ▶ B.2.b
RESET
✓ A
▶ B
  ✓ B.1
  ▶ B.2
    ✓ B.2.a
    ✓ B.2.b
RESET
✓ A
▶ B
  ✓ B.1
  ✓ B.2
RESET
✓ A
✓ B

OUTPUT
        ,
    ],
    'custom-config' => fn (): array => [
        [
            Task::make('Skip', fn (): null => null, fn (): true => true),
            TaskGroup::make('Nested', [
                Task::make('Subtask', fn (): null => null),
            ]),
        ],
        Config::make()
            // Unfortunately colors can't be tested with BufferedOutput
            ->pending('P', Color::BrightRed)
            ->running('R', Color::BrightCyan)
            ->completed('C', Color::BrightMagenta)
            ->skipped('S', Color::BrightYellow)
            ->setIndent(4),
        <<<'OUTPUT'
P Skip
P Nested
RESET
S Skip
P Nested
RESET
S Skip
R Nested
    P Subtask
RESET
S Skip
R Nested
    R Subtask
RESET
S Skip
R Nested
    C Subtask
RESET
S Skip
C Nested

OUTPUT
    ],
    'updated-title' => fn (): array => [
        [
            Task::make('Task', function (Context $ctx, Task $task): void {
                $task->updateTitle('Updated');
                // It should't render if nothing was changed
                $task->updateTitle('Updated');
            }),
        ],
        Config::make(),
        <<<'OUTPUT'
⏸ Task
RESET
▶ Task
RESET
▶ Updated
RESET
✓ Updated

OUTPUT
    ],
]);
