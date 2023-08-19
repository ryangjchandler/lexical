<?php

namespace RyanChandler\Lexical\Tests\Compiler;

use RyanChandler\Lexical\Attributes\Error;
use RyanChandler\Lexical\Attributes\Lexer;
use RyanChandler\Lexical\Attributes\Literal;
use RyanChandler\Lexical\Attributes\Regex;

#[Lexer(skip: '[ \t\n\f]+')]
enum TokenKind
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
