<?php

declare(strict_types=1);

namespace Scheel\Taskr\Config;

final class Config
{
    private function __construct(
        private string $pendingSymbol = '⏸',
        private string $runningSymbol = '▶',
        private string $completedSymbol = '✓',
        private string $skippedSymbol = '⏭',
        private Color $pendingColor = Color::Gray,
        private Color $runningColor = Color::BrightWhite,
        private Color $completedColor = Color::Green,
        private Color $skippedColor = Color::Yellow,
        private int $indent = 2,
    ) {}

    public static function make(): self
    {
        return new self;
    }

    public function pending(string $symbol, Color $color): self
    {
        $this->pendingSymbol = $symbol;
        $this->pendingColor = $color;

        return $this;
    }

    public function running(string $symbol, Color $color): self
    {
        $this->runningSymbol = $symbol;
        $this->runningColor = $color;

        return $this;
    }

    public function completed(string $symbol, Color $color): self
    {
        $this->completedSymbol = $symbol;
        $this->completedColor = $color;

        return $this;
    }

    public function skipped(string $symbol, Color $color): self
    {
        $this->skippedSymbol = $symbol;
        $this->skippedColor = $color;

        return $this;
    }

    public function setIndent(int $indent): self
    {
        $this->indent = $indent;

        return $this;
    }

    public function getPendingSymbol(): string
    {
        return $this->pendingSymbol;
    }

    public function getRunningSymbol(): string
    {
        return $this->runningSymbol;
    }

    public function getCompletedSymbol(): string
    {
        return $this->completedSymbol;
    }

    public function getSkippedSymbol(): string
    {
        return $this->skippedSymbol;
    }

    public function getPendingColor(): Color
    {
        return $this->pendingColor;
    }

    public function getRunningColor(): Color
    {
        return $this->runningColor;
    }

    public function getCompletedColor(): Color
    {
        return $this->completedColor;
    }

    public function getSkippedColor(): Color
    {
        return $this->skippedColor;
    }

    public function getIndent(): int
    {
        return $this->indent;
    }
}
