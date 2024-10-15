<?php

namespace RyanChandler\Lexical\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Lexer
{
    public function __construct(
        public readonly ?string $skip = null,
    ) {}
}
