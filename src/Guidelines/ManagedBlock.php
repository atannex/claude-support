<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Guidelines;

/**
 * The marker-delimited region of CLAUDE.md this package owns.
 * Everything outside the markers belongs to the project and is preserved byte for byte.
 */
final class ManagedBlock
{
    public const START = '<!-- atannex/claude-support:start -->';

    public const END = '<!-- atannex/claude-support:end -->';

    /** Insert or replace the block; appends it when the document has none. */
    public static function splice(string $document, string $body): string
    {
        // Keep the document's own line endings so a CRLF checkout stays CRLF.
        $eol = str_contains($document, "\r\n") ? "\r\n" : "\n";
        $block = str_replace("\n", $eol, self::START."\n".rtrim(str_replace("\r\n", "\n", $body))."\n".self::END);

        $range = self::locate($document);
        if ($range !== null) {
            return substr_replace($document, $block, $range[0], $range[1]);
        }

        $document = rtrim($document);

        return ($document === '' ? '' : $document.$eol.$eol).$block.$eol;
    }

    /** Remove the block and the blank lines it leaves behind. */
    public static function strip(string $document): string
    {
        $range = self::locate($document);
        if ($range === null) {
            return $document;
        }

        [$start, $length] = $range;
        $eol = str_contains($document, "\r\n") ? "\r\n" : "\n";
        $before = rtrim(substr($document, 0, $start));
        $after = ltrim(substr($document, $start + $length));

        $result = $before.($before !== '' && $after !== '' ? $eol.$eol : '').$after;

        return $result === '' ? '' : rtrim($result).$eol;
    }

    public static function has(string $document): bool
    {
        return self::locate($document) !== null;
    }

    /** @return array{int, int}|null offset and length of the block, markers included */
    private static function locate(string $document): ?array
    {
        $start = strpos($document, self::START);
        if ($start === false) {
            return null;
        }

        $end = strpos($document, self::END, $start);
        if ($end === false) {
            return null;
        }

        return [$start, $end + strlen(self::END) - $start];
    }
}
