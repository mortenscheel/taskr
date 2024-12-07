<?php

declare(strict_types=1);

namespace Scheel\Taskr;

use RuntimeException;

use function array_key_exists;
use function is_int;

final class Context
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private array $data = []) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data = []): self
    {
        return new self($data);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function increment(string $key, int $amount = 1): void
    {
        if ($this->has($key)) {
            $current = $this->get($key);
            if (! is_int($current)) {
                throw new RuntimeException('Attempt to increment a non-integer value');
            }
            $this->set($key, $current + $amount);
        } else {
            $this->set($key, $amount);
        }
    }
}
