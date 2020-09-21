<?php


namespace litek\pffa\utils;


use pocketmine\utils\TextFormat;

class Padding
{

    public const PADDING_LINE = 0;
    public const PADDING_CENTER = 1;

    /** @var int */
    public const lineLength = 30;

    /** @var int */
    public const charWidth = 6;

    /** @var string */
    public const spaceChar = ' ';

    /** @var int[] */
    public const charWidths = [
        ' ' => 4,
        '!' => 2,
        '"' => 5,
        '\'' => 3,
        '(' => 5,
        ')' => 5,
        '*' => 5,
        ',' => 2,
        '.' => 2,
        ':' => 2,
        ';' => 2,
        '<' => 5,
        '>' => 5,
        '@' => 7,
        'I' => 4,
        '[' => 4,
        ']' => 4,
        'f' => 5,
        'i' => 2,
        'k' => 5,
        'l' => 3,
        't' => 4,
        '' => 5,
        '|' => 2,
        '~' => 7,
        '█' => 9,
        '░' => 8,
        '▒' => 9,
        '▓' => 9,
        '▌' => 5,
        '─' => 9
    ];

    /**
     * @param string $input
     * @return string
     */
    public static function centerLine(string $input): string
    {
        return self::centerText($input, self::lineLength * self::charWidth);
    }

    /**
     * @param string $input
     * @param int $maxLength
     * @param bool $addRightPadding
     * @return string
     */
    public static function centerText(string $input, int $maxLength = 0, bool $addRightPadding = false): string
    {
        $lines = explode("\n", trim($input));

        $sortedLines = $lines;
        usort($sortedLines, static function (string $a, string $b) {
            return self::getPixelLength($b) <=> self::getPixelLength($a);
        });

        $longest = $sortedLines[0];

        if ($maxLength === 0) {
            $maxLength = self::getPixelLength($longest);
        }

        $result = '';

        $spaceWidth = self::getCharWidth(self::spaceChar);

        foreach ($lines as $sortedLine) {
            $len = max($maxLength - self::getPixelLength($sortedLine), 0);
            $padding = (int)round($len / (2 * $spaceWidth));
            $paddingRight = (int)floor($len / (2 * $spaceWidth));
            $result .= str_pad(self::spaceChar, $padding) . $sortedLine . ($addRightPadding ? str_pad(self::spaceChar, $paddingRight) : '') . "\n";
        }

        $result = rtrim($result, "\n");

        return $result;
    }

    /**
     * @param string $line
     * @return int
     */
    public static function getPixelLength(string $line): int
    {
        $length = 0;
        foreach (str_split(TextFormat::clean($line)) as $c) {
            $length += self::getCharWidth($c);
        }

        // +1 for each bold character
        $length += substr_count($line, TextFormat::BOLD);
        return $length;
    }

    /**
     * @param string $c
     * @return int
     */
    private static function getCharWidth(string $c): int
    {
        return self::charWidths[$c] ?? self::charWidth;
    }
}