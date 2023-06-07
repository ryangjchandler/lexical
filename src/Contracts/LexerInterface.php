<?php

namespace RyanChandler\Lexical\Contracts;

/**
 * @template T
 */
interface LexerInterface
{
    /**
     * Generate an array of tokens from the given input.
     *
     * @param string $input
     * @return array<T>
     *
     * @throws \RyanChandler\Lexical\Exceptions\UnexpectedCharacterException
     */
    public function tokenise(string $input): array;
}
