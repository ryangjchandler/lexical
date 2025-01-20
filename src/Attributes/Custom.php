<?php

namespace RyanChandler\Lexical\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
/**
 * @template T of \RyanChandler\Lexical\Contracts\TokenLexerInterface
 */
class Custom
{
    /**
     * @param class-string<T> $lexer
     */
    public function __construct(
        public readonly string $lexer
    ) {}
}
