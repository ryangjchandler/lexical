<?php

namespace RyanChandler\Lexical\Lexers;

use Closure;
use RyanChandler\Lexical\Contracts\LexerInterface;
use RyanChandler\Lexical\Exceptions\UnexpectedCharacterException;
use UnitEnum;

/**
 * @template T of \UnitEnum
 * @template U
 * @implements LexerInterface<U>
 */
class RuntimeLexer implements LexerInterface
{
    /** @var array<string, T> */
    protected array $patterns;

    /** @var Closure(T, string): U */
    protected Closure $produceTokenUsing;

    protected string $skip;

    /** @var T|null */
    protected ?UnitEnum $errorType = null;

    protected string $regex;

    /** @var array<int, T> */
    protected array $positionToTokenType;

    /**
     * @param array<string, T> $patterns
     * @param Closure(T, string): U $produceTokensUsing
     * @param string $skip A regular expression describing patterns that should be skipped.
     */
    public function __construct(array $patterns, Closure $produceTokenUsing, ?string $skip = null, ?UnitEnum $errorType = null)
    {
        $this->patterns = $patterns;
        $this->produceTokenUsing = $produceTokenUsing;
        $this->skip = $skip;
        $this->errorType = $errorType;
        $this->regex = '/(' . implode(')|(', array_keys($patterns)) . ')/A';
        $this->positionToTokenType = array_values($patterns);
    }

    /** {@inheritDoc} */
    public function tokenise(string $input): array
    {
        $tokens = [];
        $offset = 0;

        while (isset($input[$offset])) {
            if (preg_match($this->regex, $input, $matches, PREG_UNMATCHED_AS_NULL, $offset) === false || $matches === []) {
                if ($this->skip !== null) {
                    preg_match($this->skip, $input, $skipMatches, 0, $offset);

                    if (isset($skipMatches[0])) {
                        $offset += strlen($skipMatches[0]);
                        continue;
                    }
                }

                if ($this->errorType === null) {
                    throw UnexpectedCharacterException::make($input[$offset], $offset);
                }

                $token = $this->findNextMatchAndProduceError($input, $offset);
            } else {
                for ($i = 1; null === $matches[$i]; ++$i);

                $token = [$matches[0], $this->positionToTokenType[$i - 1]];
            }

            $tokens[] = call_user_func($this->produceTokenUsing, $token[1], $token[0]);
            $offset += strlen($matches[0]);
        }

        return $tokens;
    }

    protected function findNextMatchAndProduceError(string $input, int &$offset): array
    {
        $offsets = [];

        foreach ($this->patterns as $pattern => $_) {
            if (! preg_match('/'.$pattern.'/', $input, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                continue;
            }

            $offsets[] = $matches[0][1];
        }

        $skipped = count($offsets) > 0
            ? substr($input, $offset, min($offsets) - $offset)
            : substr($input, $offset, strlen($input) - $offset);

        $offset = count($offsets) > 0 ? min($offsets) : ($offset + (strlen($input) - $offset));

        return [$skipped, $this->errorType];
    }
}
