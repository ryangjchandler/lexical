<?php

namespace RyanChandler\Lexical\Exceptions;

use Exception;

class UnexpectedCharacterException extends Exception
{
    public readonly string $character;

    public readonly int $position;

    public static function make(string $character, int $position): static
    {
        $exception = new static(sprintf('Unexpected character "%s" at position %d', $character, $position));

        $exception->character = $character;
        $exception->position = $position;

        return $exception;
    }
}
