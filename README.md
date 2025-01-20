# Lexical

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ryangjchandler/lexical.svg?style=flat-square)](https://packagist.org/packages/ryangjchandler/lexical)
[![Tests](https://img.shields.io/github/actions/workflow/status/ryangjchandler/lexical/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ryangjchandler/lexical/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ryangjchandler/lexical.svg?style=flat-square)](https://packagist.org/packages/ryangjchandler/lexical)

## Installation

You can install the package via Composer:

```bash
composer require ryangjchandler/lexical
```

## Usage

Let's write a simple lexer for mathematical expressions. The expressions can contain numbers (only integers) and a handful of operators (`+`, `-`, `*`, `/`).

Begin by creating a new enumeration that describes the token types.

```php
enum TokenType
{
    case Number;
    case Add;
    case Subtract;
    case Multiply;
    case Divide;
}
```

Lexical provides a set of attributes that can be added to each case in an enumeration:
* `Regex` - accepts a single regular expression.
* `Literal` - accepts a string of continuous characters.
* `Error` - designates a specific enumeration case as the "error" type.

Using those attributes with `TokenType` looks like this.

```php
enum TokenType
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
}
```

With the attributes in place, we can start to build a lexer using the `LexicalBuilder`.

```php
$lexer = (new LexicalBuilder)
    ->readTokenTypesFrom(TokenType::class)
    ->build();
```

The `readTokenTypesFrom()` method is used to tell the builder where we should look for the various tokenising attributes. The `build()` method will take those attributes and return an object that implements `LexerInterface`, configured to look for the specified token types.

Then it's just a case of calling the `tokenise()` method on the lexer object to retrieve an array of tokens.

```php
$tokens = $lexer->tokenise('1+2'); // -> [[TokenType::Number, '1', Span(0, 1)], [TokenType::Add, '+', Span(1, 2)], [TokenType::Number, '2', Span(2, 3)]]
```

The `tokenise()` method returns a list of tuples, where the first item is the "type" (`TokenType` in this example), the second item is the "literal" (a string containing the matched characters) and the third item is the "span" of the token (the start and end positions in the original string).

### Skipping whitespace and other patterns

Continuing with the example of a mathematical expression, the lexer currently understands `1+2` but it would fail to tokenise `1 + 2` (added whitespace). This is because by default it expects each and every possible character to fall into a pattern.

The whitespace is insignificant in this case, so can be skipped safely. To do this, we need to add a new `Lexer` attribute to the `TokenType` enumeration and pass through a regular expression that matches the characters we want to skip.

```php
#[Lexer(skip: "[ \t\n\f]+")]
enum TokenType
{
    // ...
}
```

Now the lexer will skip over any whitespace characters and successfully tokenise `1 + 2`.

### Error handling

When a lexer encounters an unexpected character, it will throw an `UnexpectedCharacterException`.

```php
try {
    $tokens = $lexer->tokenise();
} catch (UnexpectedCharacterException $e) {
    dd($e->character, $e->position);
}
```

As mentioned above, there is an `Error` attribute that can be used to mark an enum case as the "error" type.

```php
enum TokenType
{
    // ...

    #[Error]
    case Error;
}
```

Now when the input is tokenised, the unrecognised character will be consumed like other tokens and will have a type of `TokenType::Error`.

```php
$tokens = $lexer->tokenise('1 % 2'); // -> [[TokenType::Number, '1'], [TokenType::Error, '%'], [TokenType::Number, '2']]
```

### Custom `Token` objects

If you prefer to work with dedicated objects instead of Lexical's default tuple values for each token, you can provide a custom callback to map the matched token type and literal into a custom object.

```php
class Token
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $literal,
        public readonly Span $span,
    ) {}
}

$lexer = (new LexicalBuilder)
    ->readTokenTypesFrom(TokenType::class)
    ->produceTokenUsing(fn (TokenType $type, string $literal, Span $span) => new Token($type, $literal, $span))
    ->build();

$lexer->tokenise('1 + 2'); // -> [Token { type: TokenType::Number, literal: "1" }, ...]
```

### Token Producers

Regular expressions and literal tokens can get you quite far when it comes to tokenisation, but there are scenarios where it would be easier to write "real" code to tokenise your input.

Lexical makes this possible by providing a Token Producer API. Token producers are regular PHP objects that implement the `RyanChandler\Lexical\Contracts\TokenProviderInterface` or `RyanChandler\Lexical\Contracts\TolerantTokenProviderInterface` interfaces.

They are attached to your token types using the `RyanChandler\Lexical\Attributes\Custom` attribute, passing through the fully-qualified name of the token producer class.

```php
use RyanChandler\Lexical\Attributes\Custom;
use RyanChandler\Lexical\InputSource;

enum Literals
{
    #[Custom(StringTokenProducer::class)]
    case String;
}

class StringTokenProducer implements TokenProducerInterface
{
    public function produce(InputSource $source): ?string
    {
        // 
    }
}
```

The `InputSource` object provided to the `produce()` method can be used to determine whether or not a token can be produced at the current offset. It comes with a range of utility methods such as `current()`, `peek()` and `match()`.

If your token type has an `Error` case defined, your token producer will need to implement the `TolerantTokenProducerInterface` instead. This interface has an additional method, `canProduce()`, which is used to determine whether or not the token can be seen anywhere in the remaining input.

Here's an example token producer that tokenises double-quoted strings.

```php
class StringTokenProducer implements TolerantTokenProducerInterface
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
        // If we're not looking at a double quote, return since we can't produce a token here.
        if ($source->current() !== '"') {
            return null;
        }

        // Place an offset marker in case we need to rewind at any point.
        $source->mark();

        // Consume the " character.
        $token = $source->consume();

        while ($source->current() !== '"') {
            // If we reach the end of the file before we find a closing double-quote,
            // we can rewind to the marker and return early.
            if ($source->isEof()) {
                $source->rewind();

                return null;
            }

            // Consume the current character.
            $token .= $source->consume();
        }

        // If we reach this point, we must be at a double-quote character since the
        // loop above has finished and we haven't returned yet.
        $token .= $source->consume();

        // Return the consumed text and let the Lexer handle the rest.
        return $token;
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ryan Chandler](https://github.com/ryangjchandler)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
