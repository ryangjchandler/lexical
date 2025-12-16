<?php

namespace RyanChandler\Lexical\Lexers;

use Closure;
use RyanChandler\Lexical\Contracts\LexerInterface;
use RyanChandler\Lexical\Contracts\TokenProducerInterface;
use RyanChandler\Lexical\Contracts\TolerantTokenProducerInterface;
use RyanChandler\Lexical\Exceptions\InvalidTokenProducerException;
use RyanChandler\Lexical\Exceptions\UnexpectedCharacterException;
use RyanChandler\Lexical\InputSource;
use RyanChandler\Lexical\Span;
use SplObjectStorage;
use UnitEnum;

class RuntimeLexer implements LexerInterface
{
    protected string $regex;

    protected array $markToTypeMap;

    /**
     * @param  SplObjectStorage<\UnitEnum, class-string<\RyanChandler\Lexical\Contracts\TokenProducerInterface>>  $customs
     */
    public function __construct(
        protected array $patterns,
        protected Closure $produceTokenUsing,
        protected ?string $skip = null,
        protected ?UnitEnum $errorType = null,
        protected SplObjectStorage $customs = new SplObjectStorage,
    ) {
        $regex = '/';
        $mark = 'a';
        $this->markToTypeMap = [];

        foreach ($patterns as $pattern => $type) {
            if ($regex !== '/') {
                $regex .= '|';
            }

            $regex .= "(?<{$mark}>{$pattern})";
            $this->markToTypeMap[$mark] = $type;
            $mark = str_increment($mark);
        }

        $this->regex = $regex.'/A';
    }

    public function tokenise(string $input): array
    {
        $source = new InputSource($input);
        $tokens = [];

        while (! $source->isEof()) {
            if ($this->skip !== null) {
                $skips = $source->match('/'.$this->skip.'/A');

                if (isset($skips[0])) {
                    $source->skip(strlen($skips[0]));

                    continue;
                }
            }

            $token = $this->nextMatch($source);

            $start = $source->offset();

            $source->skip(strlen($token[0]));

            $tokens[] = call_user_func($this->produceTokenUsing, $token[1], $token[0], new Span($start, $source->offset()));
        }

        return $tokens;
    }

    protected function nextMatch(InputSource $source): array
    {
        $matches = $source->match($this->regex, PREG_UNMATCHED_AS_NULL);

        if ((bool) $matches) {
            for ($m = 'a'; $matches[$m] === null; $m = str_increment($m));

            return [$matches[$m], $this->markToTypeMap[$m]];
        }

        foreach ($this->customs as $case) {
            $source->mark();

            $producer = $this->createProducer($this->customs[$case]);

            $result = $producer->produce($source);

            $source->rewind();

            if ($result === null) {
                continue;
            }

            return [$result, $case];
        }

        if ($this->errorType === null) {
            throw UnexpectedCharacterException::make($source->current(), $source->offset());
        }

        return $this->findNextMatchAndProduceError($source);
    }

    protected function createProducer(string $producer): TokenProducerInterface|TolerantTokenProducerInterface
    {
        if (! class_exists($producer)) {
            throw InvalidTokenProducerException::classDoesntExist($producer);
        }

        $producer = new $producer;

        if ($this->errorType !== null && ! $producer instanceof TolerantTokenProducerInterface) {
            throw InvalidTokenProducerException::classDoesntImplementInterface($producer::class, TolerantTokenProducerInterface::class);
        } elseif (! $producer instanceof TokenProducerInterface) {
            throw InvalidTokenProducerException::classDoesntImplementInterface($producer::class, TokenProducerInterface::class);
        }

        return $producer;
    }

    protected function findNextMatchAndProduceError(InputSource $source): array
    {
        $patterns = [...array_keys($this->patterns), $this->skip];
        $offsets = [];

        foreach ($patterns as $pattern) {
            if ($pattern === null) {
                continue;
            }

            $matches = $source->match('/'.$pattern.'/', PREG_OFFSET_CAPTURE);

            if (! $matches) {
                continue;
            }

            $offsets[] = $matches[0][1];
        }

        foreach ($this->customs as $case) {
            $producer = $this->createProducer($this->customs[$case]);
            $offset = $producer->canProduce($source);

            if ($offset === false) {
                continue;
            }

            $offsets[] = $offset;
        }

        $skipped = count($offsets) > 0
            ? $source->substr($source->offset(), min($offsets) - $source->offset())
            : $source->substr($source->offset(), $source->length() - $source->offset());

        return [$skipped, $this->errorType];
    }
}
