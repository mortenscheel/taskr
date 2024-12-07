<?php

declare(strict_types=1);

namespace Scheel\Taskr;

use Scheel\Taskr\Config\Config;
use Scheel\Taskr\Tasks\State;
use Scheel\Taskr\Tasks\TaskGroup;
use Scheel\Taskr\Tasks\TaskrItem;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

use function mb_strimwidth;
use function sprintf;

final class Renderer
{
    private readonly Terminal $terminal;

    private readonly Cursor $cursor;

    private ?string $previousMessage = null;

    public function __construct(
        private readonly OutputInterface $output,
        /** @var TaskrItem[] */
        private readonly array $tasks,
        private readonly Config $config,
    ) {
        $this->terminal = new Terminal;
        $this->cursor = new Cursor($this->output);
    }

    public function render(): void
    {
        $message = '';
        foreach ($this->tasks as $task) {
            if ($task instanceof TaskGroup) {
                $message .= $this->formatGroup($task);
            } else {
                $message .= $this->formatTask($task);
            }
        }
        if ($this->previousMessage !== null && $this->previousMessage !== '' && $this->previousMessage !== '0') {
            if ($this->previousMessage === $message) {
                return;
            }
            $this->reset($this->previousMessage);
        }
        $this->output->write($message);
        $this->previousMessage = $message;
    }

    private function formatGroup(TaskGroup $group, int $level = 0): string
    {
        $formatted = $this->formatTask($group, $level);
        if ($group->getState() !== State::Running) {
            return $formatted;
        }
        foreach ($group->getTasks() as $task) {
            if ($task instanceof TaskGroup) {
                $formatted .= $this->formatGroup($task, $level + 1);
            } else {
                $formatted .= $this->formatTask($task, $level + 1);
            }
        }

        return $formatted;
    }

    private function formatTask(TaskrItem $task, int $level = 0): string
    {
        $indent = str_repeat(' ', $level * $this->config->getIndent());
        $state = $task->getState();
        $symbol = match ($state) {
            State::Pending => $this->config->getPendingSymbol(),
            State::Running => $this->config->getRunningSymbol(),
            State::Completed => $this->config->getCompletedSymbol(),
            State::Skipped => $this->config->getSkippedSymbol(),
        };
        $color = match ($state) {
            State::Pending => $this->config->getPendingColor(),
            State::Running => $this->config->getRunningColor(),
            State::Completed => $this->config->getCompletedColor(),
            State::Skipped => $this->config->getSkippedColor(),
        };
        $title = $this->truncateTitle($task->getTitle(), $level);

        return sprintf(
            '%s<fg=%s>%s</> %s%s',
            $indent,
            $color->value,
            $symbol,
            $title,
            PHP_EOL
        );
    }

    private function reset(string $previousMessage): void
    {
        $lineCount = substr_count($previousMessage, PHP_EOL);
        for ($i = 0; $i < $lineCount; $i++) {
            $this->cursor->moveUp();
            $this->cursor->clearLine();
        }
    }

    private function truncateTitle(string $title, int $level): string
    {
        $width = $this->terminal->getWidth() - $level * 2 + 2;

        return mb_strimwidth($title, 0, $width);
    }
}
