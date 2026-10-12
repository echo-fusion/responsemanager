<?php

declare(strict_types=1);

namespace EchoFusion\ResponseManager;

use EchoFusion\ResponseManager\Concerns\InjectsDefaultHeader;
use GuzzleHttp\Psr7\Response;

class XmlResponse extends Response
{
    use InjectsDefaultHeader;

    /**
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(string $xml, int $status = 200, array $headers = [])
    {
        parent::__construct(
            $status,
            self::withDefaultHeader('Content-Type', 'application/xml; charset=utf-8', $headers),
            $xml,
        );
    }
}
