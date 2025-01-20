<?php

namespace RyanChandler\Lexical\Contracts;

use RyanChandler\Lexical\InputSource;

interface TokenProducerInterface
{
    /**
     * Use the input source provided to tokenise the input and return the tokenised string.
     *
     * If nothing can be tokenise, return null.
     */
    public function produce(InputSource $source): ?string;
}
