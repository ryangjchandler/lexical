<?php

namespace RyanChandler\Lexical;

class Span
{
    public function __construct(
        public int $start,
        public int $end,
    ) {}
}
