<?php

declare(strict_types=1);

namespace EchoFusion\ResponseManager;

use EchoFusion\ResponseManager\Concerns\InjectsDefaultHeader;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use const ENT_QUOTES;
use function htmlspecialchars;
use function sprintf;

class RedirectResponse extends Response
{
    use InjectsDefaultHeader;

    /**
     * @param array<string, string|list<string>> $headers
     *
     * @throws InvalidArgumentException if the status is not a 3xx code
     */
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        if ($status < 300 || $status > 399) {
            throw new InvalidArgumentException(sprintf('A redirect status must be 3xx, %d given.', $status));
        }

        parent::__construct(
            $status,
            self::withDefaultHeader('Location', $url, self::withDefaultHeader('Content-Type', 'text/html; charset=utf-8', $headers)),
            self::createBody($url),
        );
    }

    private static function createBody(string $url): string
    {
        $escaped = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<!DOCTYPE html><html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=\'%1$s\'" />'
            . '<title>Redirecting to %1$s</title></head><body>Redirecting to <a href="%1$s">%1$s</a>.</body></html>',
            $escaped,
        );
    }
}
