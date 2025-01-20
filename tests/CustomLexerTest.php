<?php

use RyanChandler\Lexical\Attributes\Custom;
use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Lexer;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\Contracts\TokenProducerInterface;
use RyanChandler\Lexical\Contracts\TolerantTokenProducerInterface;
use RyanChandler\Lexical\InputSource;
use RyanChandler\Lexical\LexicalBuilder;
use RyanChandler\Lexical\Span;

it('can provide a custom lexer for a specific token type', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(TokenWithCustom::class)
        ->build();

    $tokens = $lexer->tokenise('123 "Hello, world!" 123');

    expect($tokens)
        ->toMatchArray([
            [TokenWithCustom::Number, '123', new Span(0, 3)],
            [TokenWithCustom::String, '"Hello, world!"', new Span(4, 19)],
            [TokenWithCustom::Number, '123', new Span(20, 23)],
        ]);
});

it('can provide a custom lexer for a specific token type with error kind', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(TokenWithCustomAndError::class)
        ->build();

    $tokens = $lexer->tokenise('123 hello"Hello, world!"');

    expect($tokens)
        ->toMatchArray([
            [TokenWithCustomAndError::Number, '123', new Span(0, 3)],
            [TokenWithCustomAndError::Error, 'hello', new Span(4, 9)],
            [TokenWithCustomAndError::String, '"Hello, world!"', new Span(9, 24)],
        ]);
});

class StringLexer implements TolerantTokenProducerInterface
{
    public function canProduce(InputSource $source): int|false
    {
        $matches = $source->match('/"/', PREG_OFFSET_CAPTURE);

        if (! $matches) {
            return false;
        }

        return $matches[0][1];
    }

    public function produce(InputSource $source): ?string
    {
        if ($source->current() !== '"') {
            return null;
        }

        $source->mark();

        $token = $source->consume();

        while ($source->current() !== '"') {
            if ($source->isEof()) {
                $source->reset();

                return null;
            }

            $token .= $source->consume();
        }

        $token .= $source->consume();

        return $token;
    }
}

#[Lexer(skip: '\s+')]
enum TokenWithCustom
{
    #[Regex('[0-9]+')]
    case Number;

    #[Custom(StringLexer::class)]
    case String;
}

#[Lexer(skip: '\s+')]
enum TokenWithCustomAndError
{
    #[Regex('[0-9]+')]
    case Number;

    #[Custom(StringLexer::class)]
    case String;

    #[Error]
    case Error;
}
