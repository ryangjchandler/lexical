<?php

namespace RyanChandler\Lexical;

use Closure;
use UnitEnum;
use Exception;
use ReflectionEnum;
use ReflectionException;
use ReflectionEnumUnitCase;
use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Lexer;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\Attributes\Literal;
use RyanChandler\Lexical\Lexers\RuntimeLexer;
use RyanChandler\Lexical\Contracts\LexerInterface;

/**
 * @template T of \UnitEnum
 * @template U
 */
class LexicalBuilder
{
    /** @var class-string<T> */
    protected string $readDefinitionsFrom;

    /** @var Closure(T, string): U */
    protected Closure $produceTokenUsing;

    public function __construct()
    {
        $this->produceTokenUsing = fn (UnitEnum $type, string $literal) => [$type, $literal];
    }

    /**
     * Specify which enumeration should be used to locate definitions.
     *
     * @param  class-string<T>  $class
     * @return static
     */
    public function readTokenTypesFrom(string $class): static
    {
        $this->readDefinitionsFrom = $class;

        return $this;
    }

    /**
     * Provide a callback to change how tokens are represented.
     *
     * @param  Closure(T, string): U  $closure
     * @return static
     */
    public function produceTokenUsing(Closure $callback): static
    {
        $this->produceTokenUsing = $callback;

        return $this;
    }

    public function build(): LexerInterface
    {
        try {
            $reflection = new ReflectionEnum($this->readDefinitionsFrom);
        } catch (ReflectionException) {
            throw new Exception('The definition source must be an enum.');
        }

        $derived = $reflection->getAttributes(Lexer::class)[0] ?? null;
        $skip = null;

        if ($derived !== null) {
            $skip = $derived->newInstance()->skip;
        }

        $cases = $this->readDefinitionsFrom::cases();
        $errorCase = null;
        $patterns = [];

        foreach ($cases as $case) {
            $caseReflection = new ReflectionEnumUnitCase($this->readDefinitionsFrom, $case->name);
            $literal = $caseReflection->getAttributes(Literal::class)[0] ?? null;

            if ($literal !== null) {
                $patterns[preg_quote($literal->newInstance()->literal, '/')] = $case;
                continue;
            }

            $regex = $caseReflection->getAttributes(Regex::class)[0] ?? null;

            if ($regex !== null) {
                $patterns[$regex->newInstance()->pattern] = $case;
                continue;
            }

            $error = $caseReflection->getAttributes(Error::class)[0] ?? null;

            if ($error !== null) {
                $errorCase = $case;
                continue;
            }
        }

        return new RuntimeLexer($patterns, $this->produceTokenUsing, '/'.$skip.'/A', $errorCase);
    }
}
