<?php

declare(strict_types=1);

namespace Scheel\Taskr\Tasks;

use Scheel\Taskr\Context;
use Scheel\Taskr\Taskr;

interface TaskrItem
{
    public function getState(): State;

    public function getTitle(): string;

    public function setSkipped(): void;

    public function execute(Context $context): State;

    public function setManager(Taskr $manager): void;
}
