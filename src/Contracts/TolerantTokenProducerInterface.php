<?php

namespace RyanChandler\Lexical\Contracts;

use RyanChandler\Lexical\InputSource;

interface TolerantTokenProducerInterface extends TokenProducerInterface
{
    /**
     * Determine whether or not this token producer can produce a token
     * somewhere in the input source.
     * 
     * This is required for lexers that are error-tolerant and need to scan
     * ahead to determine if they can produce a token or not.
     * 
     * @return int|false The offset in the input source where the token could be produced, or false if it cannot be produced.
     */
    public function canProduce(InputSource $source): int|false;
}
