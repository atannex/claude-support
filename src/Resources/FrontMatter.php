<?php

declare(strict_types=1);

namespace Atannex\ClaudeSupport\Resources;

/**
 * Reads the flat `key: value` YAML front matter Claude Code uses.
 * Nested YAML is deliberately unsupported: Claude Code doesn't use it here.
 */
final class FrontMatter
{
    /** @return array<string, string> */
    public static function parse(string $markdown): array
    {
        $markdown = str_replace("\r\n", "\n", $markdown);

        if (preg_match('/\A\x{FEFF}?---\n(.*?)\n---(\n|\z)/su', $markdown, $block) !== 1) {
            return [];
        }

        $values = [];
        foreach (explode("\n", $block[1]) as $line) {
            if (preg_match('/^([A-Za-z][\w-]*):\s*(.*)$/', $line, $match) === 1) {
                $values[$match[1]] = self::unquote(trim($match[2]));
            }
        }

        return $values;
    }

    private static function unquote(string $value): string
    {
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
