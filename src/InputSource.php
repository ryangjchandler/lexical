<?php

namespace RyanChandler\Lexical;

class InputSource
{
    protected int $offset = 0;

    protected int $length;

    protected ?int $mark = null;

    public function __construct(
        protected readonly string $source,
    ) {
        $this->length = strlen($source);
    }

    public function isEof(): bool
    {
        return $this->offset >= $this->length;
    }

    public function current(): ?string
    {
        return $this->source[$this->offset] ?? null;
    }

    public function peek(): ?string
    {
        return $this->source[$this->offset + 1] ?? null;
    }

    public function skip(int $n): void
    {
        $this->offset += $n;
    }

    public function matches(string $pattern, int $flags = 0): bool
    {
        return preg_match($pattern, $this->source, $matches, $flags, $this->offset) === 1;
    }

    public function match(string $pattern, int $flags = 0): ?array
    {
        preg_match($pattern, $this->source, $matches, $flags, $this->offset);

        return $matches;
    }

    public function consume(): ?string
    {
        return $this->source[$this->offset++] ?? null;
    }

    public function mark(): void
    {
        $this->mark = $this->offset;
    }

    public function rewind(): void
    {
        if ($this->mark === null) {
            return;
        }

        $this->offset = $this->mark;
        $this->mark = null;
    }

    public function remaining(): string
    {
        return substr($this->source, $this->offset);
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function length(): int
    {
        return $this->length;
    }

    public function substr(int $offset, int $length): string
    {
        return substr($this->source, $offset, $length);
    }
}
