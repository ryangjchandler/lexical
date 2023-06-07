<?php

use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Lexer;
use RyanChandler\Lexical\Attributes\Literal;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\LexicalBuilder;

it('can produce the correct tokens for the given math expression', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MathTestToken::class)
        ->build();

    expect($lexer->tokenise('1 + 2 - 3 * 4 / 5'))
        ->toBe([
            [MathTestToken::Number, '1'],
            [MathTestToken::Add, '+'],
            [MathTestToken::Number, '2'],
            [MathTestToken::Subtract, '-'],
            [MathTestToken::Number, '3'],
            [MathTestToken::Multiply, '*'],
            [MathTestToken::Number, '4'],
            [MathTestToken::Divide, '/'],
            [MathTestToken::Number, '5'],
        ]);
});

it('produces the specified error token type when encountering unexpected character', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MathTestToken::class)
        ->build();

    expect($lexer->tokenise('1 % 2'))
        ->toBe([
            [MathTestToken::Number, '1'],
            [MathTestToken::Error, '%'],
            [MathTestToken::Number, '2'],
        ]);
});

#[Lexer(skip: '[ \t\n\f]+')]
enum MathTestToken
{
    #[Regex("[0-9]+")]
    case Number;

    #[Literal("+")]
    case Add;

    #[Literal("-")]
    case Subtract;

    #[Literal("*")]
    case Multiply;

    #[Literal("/")]
    case Divide;

    #[Error]
    case Error;
}
