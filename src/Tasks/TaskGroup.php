<?php

declare(strict_types=1);

namespace Scheel\Taskr\Tasks;

use Scheel\Taskr\Context;
use Scheel\Taskr\Taskr;

final class TaskGroup implements TaskrItem
{
    /** @var ?callable */
    private $skip;

    private State $state = State::Pending;

    private Taskr $manager;

    /**
     * @param  TaskrItem[]  $tasks
     */
    public function __construct(
        private readonly string $title,
        /** @var TaskrItem[] */
        private readonly array $tasks,
        ?callable $skip = null,
    ) {
        $this->skip = $skip;
    }

    /**
     * @param  TaskrItem[]  $tasks
     */
    public static function make(string $title, array $tasks, ?callable $skip = null): self
    {
        return new self($title, $tasks, $skip);
    }

    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @return TaskrItem[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function execute(Context $context): State
    {
        if ($this->skip !== null && call_user_func($this->skip, $context)) {
            $this->setSkipped();
            $this->manager->render();

            return $this->state;
        }
        $this->state = State::Running;
        $this->manager->render();
        foreach ($this->tasks as $task) {
            $task->execute($context);
        }
        $this->state = State::Completed;
        $this->manager->render();

        return $this->state;
    }

    public function setSkipped(): void
    {
        foreach ($this->tasks as $task) {
            $task->setSkipped();
        }
        $this->state = State::Skipped;
    }

    public function setManager(Taskr $manager): void
    {
        $this->manager = $manager;
        foreach ($this->tasks as $task) {
            $task->setManager($manager);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
