<?php

namespace RyanChandler\Lexical\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Literal
{
    public function __construct(
        public readonly string $literal,
    ) {}
}
