<?php
declare(strict_types=1);

namespace Felds\TusServerBundle\Util;

abstract class MetadataParser
{
    /**
     * Parse the tus metadata header (usually `Upload-Metadata`).
     *
     * @param string The contents of the header entry.
     * @return array<string, string> An array containing the parsed records.
     */
    public static function parse(string $metadata): array
    {
        $records = preg_split('{,}', $metadata, -1, PREG_SPLIT_NO_EMPTY);

        return array_reduce(
            $records,
            function (array $acc, $record) {
                list($name, $value) = explode(' ', trim($record));

                $acc[$name] = base64_decode($value);

                return $acc;
            },
            []
        );
    }
}
