<?php

namespace RyanChandler\Lexical\Exceptions;

use Exception;
use RyanChandler\Lexical\Contracts\TokenProducerInterface;

class InvalidTokenProducerException extends Exception
{
    public static function classDoesntExist(string $class): static
    {
        return new static("The token producer class [{$class}] does not exist.");
    }

    public static function classDoesntImplementInterface(string $class, string $interface): static
    {
        return new static(sprintf('The token producer class [%s] does not implement the %s interface.', $class, $interface));
    }
}
