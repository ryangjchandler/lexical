<?php

use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Regex;
use RyanChandler\Lexical\LexicalBuilder;

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
        ->toBe([
            [MarkdownTokenType::Text, 'Lorem '],
            [MarkdownTokenType::Bold, '**ipsum**'],
            [MarkdownTokenType::Text, ' ahmet sun']
        ]);
});
