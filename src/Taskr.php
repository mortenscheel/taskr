<?php

declare(strict_types=1);

namespace Scheel\Taskr;

use Scheel\Taskr\Config\Config;
use Scheel\Taskr\Tasks\TaskrItem;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class Taskr
{
    private Renderer $renderer;

    public function __construct(
        /** @var TaskrItem[] */
        private array $tasks,
        ?OutputInterface $output = null,
        ?Config $config = null,
    ) {
        $this->renderer = new Renderer(
            $output ?? (new ConsoleOutput)->getErrorOutput(),
            $this->tasks,
            $config ?? Config::make(),
        );
    }

    /**
     * @param  TaskrItem[]  $tasks
     */
    public static function make(
        array $tasks,
        ?OutputInterface $output = null,
        ?Config $config = null,
    ): self {
        return new self($tasks, $output, $config);
    }

    public function render(): void
    {
        $this->renderer->render();
    }

    public function run(?Context $context = null): Context
    {
        $context ??= new Context;
        foreach ($this->tasks as $task) {
            $task->setManager($this);
        }
        $this->render();
        foreach ($this->tasks as $task) {
            $task->execute($context);
        }

        return $context;
    }
}
