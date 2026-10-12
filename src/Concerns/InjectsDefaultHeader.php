<?php

declare(strict_types=1);

namespace EchoFusion\ResponseManager\Concerns;

use function array_change_key_case;
use function array_key_exists;
use function mb_strtolower;

/**
 * @internal
 */
trait InjectsDefaultHeader
{
    /**
     * Add a header unless the caller already provided it (header names are case-insensitive).
     *
     * @param array<string, string|list<string>> $headers
     *
     * @return array<string, string|list<string>>
     */
    private static function withDefaultHeader(string $name, string $value, array $headers): array
    {
        if (!array_key_exists(mb_strtolower($name), array_change_key_case($headers))) {
            $headers[$name] = $value;
        }

        return $headers;
    }
}
