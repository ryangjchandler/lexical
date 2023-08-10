<?php

use RyanChandler\Lexical\Span;
use RyanChandler\Lexical\Tests\Compiler\Lexer;
use RyanChandler\Lexical\Tests\Compiler\TokenKind;

beforeAll(function () {
    shell_exec('php ./bin/lexical -i RyanChandler\\\\Lexical\\\\Tests\\\\Compiler\\\\TokenKind -o RyanChandler\\\\Lexical\\\\Tests\\\\Compiler\\\\Lexer -p ./tests/Compiler/Lexer.php');
});

it('can produce the correct tokens for the given math expression', function () {
    $lexer = new Lexer();

    expect($lexer->tokenise('1 + 2 - 3 * 4 / 5'))
        ->toMatchArray([
            [TokenKind::Number, '1', new Span(0, 1)],
            [TokenKind::Add, '+', new Span(2, 3)],
            [TokenKind::Number, '2', new Span(4, 5)],
            [TokenKind::Subtract, '-', new Span(6, 7)],
            [TokenKind::Number, '3', new Span(8, 9)],
            [TokenKind::Multiply, '*', new Span(10, 11)],
            [TokenKind::Number, '4', new Span(12, 13)],
            [TokenKind::Divide, '/', new Span(14, 15)],
            [TokenKind::Number, '5', new Span(16, 17)],
        ]);
});
