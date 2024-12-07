<?php

declare(strict_types=1);

namespace Scheel\Taskr\Tasks;

use Scheel\Taskr\Context;
use Scheel\Taskr\Taskr;

final class Task implements TaskrItem
{
    /** @var callable */
    private $action;

    /** @var ?callable */
    private $skip;

    private State $state = State::Pending;

    private Taskr $manager;

    public function __construct(
        private string $title,
        callable $action,
        ?callable $skip = null
    ) {
        $this->action = $action;
        $this->skip = $skip;
    }

    public static function make(string $title, callable $action, ?callable $when = null): TaskrItem
    {
        return new self($title, $action, $when);
    }

    public function getState(): State
    {
        return $this->state;
    }

    public function setSkipped(): void
    {
        $this->state = State::Skipped;
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
        call_user_func($this->action, $context, $this);
        $this->state = State::Completed;
        $this->manager->render();

        return $this->state;
    }

    public function setManager(Taskr $manager): void
    {
        $this->manager = $manager;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function updateTitle(string $title): void
    {
        $this->title = $title;
        $this->manager->render();
    }
}
