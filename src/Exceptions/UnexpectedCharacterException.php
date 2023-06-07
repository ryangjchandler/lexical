<?php

namespace RyanChandler\Lexical\Exceptions;

use Exception;

class UnexpectedCharacterException extends Exception
{
    public static function make(string $character, int $position): static
    {
        return new static(sprintf('Unexpected character "%s" as position %d', $character, $position));
    }
}
