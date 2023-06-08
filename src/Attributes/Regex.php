<?php

namespace RyanChandler\Lexical\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Regex
{
    public function __construct(
        public readonly string $pattern,
    ) {
    }
}
