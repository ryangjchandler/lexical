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
