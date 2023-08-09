<?php

use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Lexer;
use RyanChandler\Lexical\Attributes\Literal;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\Exceptions\UnexpectedCharacterException;
use RyanChandler\Lexical\LexicalBuilder;
use RyanChandler\Lexical\Span;

it('can produce the correct tokens for the given math expression', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MathTestToken::class)
        ->build();

    expect($lexer->tokenise('1 + 2 - 3 * 4 / 5'))
        ->toMatchArray([
            [MathTestToken::Number, '1', new Span(0, 1)],
            [MathTestToken::Add, '+', new Span(2, 3)],
            [MathTestToken::Number, '2', new Span(4, 5)],
            [MathTestToken::Subtract, '-', new Span(6, 7)],
            [MathTestToken::Number, '3', new Span(8, 9)],
            [MathTestToken::Multiply, '*', new Span(10, 11)],
            [MathTestToken::Number, '4', new Span(12, 13)],
            [MathTestToken::Divide, '/', new Span(14, 15)],
            [MathTestToken::Number, '5', new Span(16, 17)],
        ]);
});

it('throws when encountering an expected character and no error type', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MathTestToken::class)
        ->build();

    expect(fn () => $lexer->tokenise('1 % 2'))
        ->toThrow(UnexpectedCharacterException::class, 'Unexpected character "%" at position 2');
});

it('produces the specified error token type when encountering an unexpected character', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MathTestTokenWithError::class)
        ->build();

    expect($lexer->tokenise('1 % 2'))
        ->toMatchArray([
            [MathTestTokenWithError::Number, '1', new Span(0, 1)],
            [MathTestTokenWithError::Error, '%', new Span(2, 3)],
            [MathTestTokenWithError::Number, '2', new Span(4, 5)],
        ]);
});

#[Lexer(skip: '[ \t\n\f]+')]
enum MathTestToken
{
    #[Regex('[0-9]+')]
    case Number;

    #[Literal('+')]
    case Add;

    #[Literal('-')]
    case Subtract;

    #[Literal('*')]
    case Multiply;

    #[Literal('/')]
    case Divide;
}

#[Lexer(skip: '[ \t\n\f]+')]
enum MathTestTokenWithError
{
    #[Regex('[0-9]+')]
    case Number;

    #[Literal('+')]
    case Add;

    #[Literal('-')]
    case Subtract;

    #[Literal('*')]
    case Multiply;

    #[Literal('/')]
    case Divide;

    #[Error]
    case Error;
}
