<?php

namespace RyanChandler\Lexical\Contracts;

interface LexerInterface
{
    /**
     * Generate an array of tokens from the given input.
     *
     *
     * @throws \RyanChandler\Lexical\Exceptions\UnexpectedCharacterException
     */
    public function tokenise(string $input): array;
}
