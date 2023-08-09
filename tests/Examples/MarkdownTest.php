<?php

use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\LexicalBuilder;
use RyanChandler\Lexical\Span;

enum MarkdownTokenType
{
    #[Regex("\*\*(.*?)\*\*")]
    case Bold;

    #[Error]
    case Text;
}

test('markdown > can tokenise bold', function () {
    $lexer = (new LexicalBuilder)
        ->readTokenTypesFrom(MarkdownTokenType::class)
        ->build();

    expect($lexer->tokenise('Lorem **ipsum** ahmet sun'))
        ->toMatchArray([
            [MarkdownTokenType::Text, 'Lorem ', new Span(0, 6)],
            [MarkdownTokenType::Bold, '**ipsum**', new Span(6, 15)],
            [MarkdownTokenType::Text, ' ahmet sun', new Span(15, 25)],
        ]);
});
