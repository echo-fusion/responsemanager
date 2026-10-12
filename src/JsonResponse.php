<?php

declare(strict_types=1);

namespace EchoFusion\ResponseManager;

use EchoFusion\ResponseManager\Concerns\InjectsDefaultHeader;
use EchoFusion\ResponseManager\Exceptions\JsonEncodingException;
use GuzzleHttp\Psr7\Response;
use JsonException;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use function is_object;
use function json_encode;
use function sprintf;

class JsonResponse extends Response
{
    use InjectsDefaultHeader;

    public const DEFAULT_JSON_FLAGS = JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
        | JSON_UNESCAPED_SLASHES;

    private mixed $payload;

    /**
     * @param array<string, string|list<string>> $headers
     *
     * @throws JsonEncodingException if the data cannot be encoded as JSON
     */
    public function __construct(
        mixed $data,
        int $status = 200,
        array $headers = [],
        int $encodingOptions = self::DEFAULT_JSON_FLAGS,
    ) {
        $this->payload = is_object($data) ? clone $data : $data;

        parent::__construct(
            $status,
            self::withDefaultHeader('Content-Type', 'application/json', $headers),
            self::encode($data, $encodingOptions),
        );
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    private static function encode(mixed $data, int $encodingOptions): string
    {
        try {
            return json_encode($data, $encodingOptions | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonEncodingException(sprintf('Unable to encode data to JSON: %s', $e->getMessage()), 0, $e);
        }
    }
}
