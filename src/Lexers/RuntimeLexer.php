<?php

namespace RyanChandler\Lexical\Lexers;

use Closure;
use RyanChandler\Lexical\Contracts\LexerInterface;
use RyanChandler\Lexical\Exceptions\UnexpectedCharacterException;
use UnitEnum;

class RuntimeLexer implements LexerInterface
{
    protected array $patterns;

    protected Closure $produceTokenUsing;

    protected ?string $skip;

    protected ?UnitEnum $errorType = null;

    protected string $regex;

    protected array $markToTypeMap;

    public function __construct(array $patterns, Closure $produceTokenUsing, string $skip = null, UnitEnum $errorType = null)
    {
        $this->patterns = $patterns;
        $this->produceTokenUsing = $produceTokenUsing;
        $this->skip = $skip;
        $this->errorType = $errorType;

        $regex = '/';
        $mark = 'a';
        $this->markToTypeMap = [];

        foreach ($patterns as $pattern => $type) {
            if ($regex !== '/') {
                $regex .= '|';
            }

            $regex .= "(?<{$mark}>{$pattern})";
            $this->markToTypeMap[$mark] = $type;
            $mark++;
        }

        $this->regex = $regex.'/A';
    }

    public function tokenise(string $input): array
    {
        $tokens = [];
        $offset = 0;

        while (isset($input[$offset])) {
            if ($this->skip !== null) {
                preg_match('/'.$this->skip.'/A', $input, $skips, 0, $offset);

                if (isset($skips[0])) {
                    $offset += strlen($skips[0]);

                    continue;
                }
            }

            if (! preg_match($this->regex, $input, $matches, PREG_UNMATCHED_AS_NULL, $offset)) {
                if ($this->errorType === null) {
                    throw UnexpectedCharacterException::make($input[$offset], $offset);
                }

                $token = $this->findNextMatchAndProduceError($input, $offset);
            } else {
                for ($m = 'a'; null === $matches[$m]; $m++);

                $token = [$matches[$m], $this->markToTypeMap[$m]];
            }

            $tokens[] = call_user_func($this->produceTokenUsing, $token[1], $token[0]);
            $offset += strlen($token[0]);
        }

        return $tokens;
    }

    protected function findNextMatchAndProduceError(string $input, int $offset): array
    {
        $patterns = [...array_keys($this->patterns), $this->skip];
        $offsets = [];

        foreach ($patterns as $pattern) {
            if ($pattern === null) {
                continue;
            }

            if (! preg_match('/'.$pattern.'/', $input, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                continue;
            }

            $offsets[] = $matches[0][1];
        }

        $skipped = count($offsets) > 0
            ? substr($input, $offset, min($offsets) - $offset)
            : substr($input, $offset, strlen($input) - $offset);

        return [$skipped, $this->errorType];
    }
}
