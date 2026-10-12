# ResponseManager

**ResponseManager** provides ready-made [PSR-7](https://www.php-fig.org/psr/psr-7/) response classes for the EchoFusion framework: HTML, JSON, XML and redirect responses. They extend `GuzzleHttp\Psr7\Response`, so they work anywhere a `Psr\Http\Message\ResponseInterface` is expected.

## Installation

```bash
composer require echo-fusion/responsemanager
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.1
* PHP 8.2
* PHP 8.3

## Usage

All constructors take an optional status code and headers array. A `Content-Type` (or `Location`) header you pass yourself is never overwritten, whatever its letter case.

### HTML

```php
use EchoFusion\ResponseManager\HtmlResponse;

return new HtmlResponse('<h1>Hello</h1>');           // 200, text/html; charset=utf-8
return new HtmlResponse($html, 404);
```

### JSON

```php
use EchoFusion\ResponseManager\JsonResponse;

return new JsonResponse(['id' => 1], 201);           // application/json
```

Data is encoded with safe defaults (`JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES`); pass your own flags as the fourth argument. Data that cannot be encoded throws `EchoFusion\ResponseManager\Exceptions\JsonEncodingException`. `getPayload()` returns the original data (objects are cloned).

### XML

```php
use EchoFusion\ResponseManager\XmlResponse;

return new XmlResponse('<user id="1"/>');            // application/xml; charset=utf-8
```

### Redirect

```php
use EchoFusion\ResponseManager\RedirectResponse;

return new RedirectResponse('/login');               // 302
return new RedirectResponse('/new-url', 301);
```

The status must be 3xx, otherwise an `InvalidArgumentException` is thrown. The URL is HTML-escaped in the fallback body.

## Testing

```bash
composer test
```

## Credits

- [Amir Shadanfar](https://github.com/amir-shadanfar)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
