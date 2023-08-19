<?php

namespace RyanChandler\Lexical\Tests\Compiler;

use RyanChandler\Lexical\Contracts\LexerInterface;
use RyanChandler\Lexical\Exceptions\UnexpectedCharacterException;
use RyanChandler\Lexical\Span;

// This class is auto-generated.
class Lexer implements LexerInterface
{
    const PATTERNS = [
        '[0-9]+' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Number,
        "\+" => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Add,
        "\-" => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Subtract,
        "\*" => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Multiply,
        "\/" => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Divide,

    ];

    const REGEX = '/(?<a>[0-9]+)|(?<b>\+)|(?<c>\-)|(?<d>\*)|(?<e>\/)/A';

    const SKIP = '[ \t\n\f]+';

    const MARK_TO_TYPE_MAP = [
        'a' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Number,
        'b' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Add,
        'c' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Subtract,
        'd' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Multiply,
        'e' => \RyanChandler\Lexical\Tests\Compiler\TokenKind::Divide,

    ];

    const ERROR_TYPE = \RyanChandler\Lexical\Tests\Compiler\TokenKind::Error;

    public function tokenise(string $input): array
    {
        $tokens = [];
        $offset = 0;

        while (isset($input[$offset])) {
            if (self::SKIP !== null) {
                preg_match('/'.self::SKIP.'/A', $input, $skips, 0, $offset);

                if (isset($skips[0])) {
                    $offset += strlen($skips[0]);

                    continue;
                }
            }

            if (! preg_match(self::REGEX, $input, $matches, PREG_UNMATCHED_AS_NULL, $offset)) {
                if (self::ERROR_TYPE === null) {
                    throw UnexpectedCharacterException::make($input[$offset], $offset);
                }

                $token = $this->findNextMatchAndProduceError($input, $offset);
            } else {
                for ($m = 'a'; null === $matches[$m]; $m++);

                $token = [$matches[$m], self::MARK_TO_TYPE_MAP[$m]];
            }

            $start = $offset;
            $offset += strlen($token[0]);
            $tokens[] = [$token[1], $token[0], new Span($start, $offset)];
        }

        return $tokens;
    }

    protected function findNextMatchAndProduceError(string $input, int $offset): array
    {
        $patterns = [...array_keys(self::PATTERNS), self::SKIP];
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

        return [$skipped, self::ERROR_TYPE];
    }
}
