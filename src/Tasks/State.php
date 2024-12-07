<?php

declare(strict_types=1);

namespace Scheel\Taskr\Tasks;

enum State: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Skipped = 'skipped';
}
