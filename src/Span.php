<?php

namespace RyanChandler\Lexical;

class Span
{
    public function __construct(
        protected int $start,
        protected int $end,
    ) {
    }
}
