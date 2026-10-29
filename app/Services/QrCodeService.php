<?php

namespace App\Services;

/**
 * Pure-PHP standard QR Code Generator.
 * Implements ISO/IEC 18004 specification to produce clean, scalable,
 * high-contrast SVG vector QR codes with zero external dependencies.
 */
class QrCodeService
{
    public const EC_LEVEL_L = 1; // 7% recovery

    public const EC_LEVEL_M = 0; // 15% recovery (Default / Recommended for UPI)

    public const EC_LEVEL_Q = 3; // 25% recovery

    public const EC_LEVEL_H = 2; // 30% recovery

    /**
     * Galois Field 256 Exp and Log tables.
     *
     * @var array<int, int>|null
     */
    private static ?array $gfExp = null;

    /**
     * @var array<int, int>|null
     */
    private static ?array $gfLog = null;

    /**
     * Alignment pattern center coordinates per version (Versions 1 to 14).
     *
     * @var array<int, list<int>>
     */
    private static array $alignmentCoordinates = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
        11 => [6, 30, 54],
        12 => [6, 32, 58],
        13 => [6, 34, 62],
        14 => [6, 26, 46, 66],
    ];

    /**
     * Total data capacity and error correction blocks per version (1 to 14).
     * Structure: [version => [ecLevel => [totalDataCodewords, ecCodewordsPerBlock, [numBlocksGroup1, dataCodewordsGroup1], [numBlocksGroup2, dataCodewordsGroup2]]]]
     *
     * @var array<int, array<int, array{0: int, 1: int, 2: array{0: int, 1: int}, 3?: array{0: int, 1: int}}>>
     */
    private static array $versionSpecs = [
        1 => [
            self::EC_LEVEL_L => [19, 7, [1, 19]],
            self::EC_LEVEL_M => [16, 10, [1, 16]],
            self::EC_LEVEL_Q => [13, 13, [1, 13]],
            self::EC_LEVEL_H => [9, 17, [1, 9]],
        ],
        2 => [
            self::EC_LEVEL_L => [34, 10, [1, 34]],
            self::EC_LEVEL_M => [28, 16, [1, 28]],
            self::EC_LEVEL_Q => [22, 22, [1, 22]],
            self::EC_LEVEL_H => [16, 28, [1, 16]],
        ],
        3 => [
            self::EC_LEVEL_L => [55, 15, [1, 55]],
            self::EC_LEVEL_M => [44, 26, [1, 44]],
            self::EC_LEVEL_Q => [34, 18, [2, 17]],
            self::EC_LEVEL_H => [26, 22, [2, 13]],
        ],
        4 => [
            self::EC_LEVEL_L => [80, 20, [1, 80]],
            self::EC_LEVEL_M => [64, 18, [2, 32]],
            self::EC_LEVEL_Q => [48, 26, [2, 24]],
            self::EC_LEVEL_H => [36, 16, [4, 9]],
        ],
        5 => [
            self::EC_LEVEL_L => [108, 26, [1, 108]],
            self::EC_LEVEL_M => [86, 24, [2, 43]],
            self::EC_LEVEL_Q => [62, 18, [2, 15], [2, 16]],
            self::EC_LEVEL_H => [46, 22, [2, 11], [2, 12]],
        ],
        6 => [
            self::EC_LEVEL_L => [136, 18, [2, 68]],
            self::EC_LEVEL_M => [108, 16, [4, 27]],
            self::EC_LEVEL_Q => [76, 24, [4, 19]],
            self::EC_LEVEL_H => [60, 28, [4, 15]],
        ],
        7 => [
            self::EC_LEVEL_L => [156, 20, [2, 78]],
            self::EC_LEVEL_M => [124, 18, [4, 31]],
            self::EC_LEVEL_Q => [88, 18, [2, 14], [4, 15]],
            self::EC_LEVEL_H => [66, 26, [4, 13], [1, 14]],
        ],
        8 => [
            self::EC_LEVEL_L => [194, 24, [2, 97]],
            self::EC_LEVEL_M => [154, 22, [2, 38], [2, 39]],
            self::EC_LEVEL_Q => [110, 22, [4, 18], [2, 19]],
            self::EC_LEVEL_H => [86, 26, [4, 14], [2, 15]],
        ],
        9 => [
            self::EC_LEVEL_L => [232, 30, [2, 116]],
            self::EC_LEVEL_M => [182, 22, [3, 36], [2, 37]],
            self::EC_LEVEL_Q => [132, 20, [4, 16], [4, 17]],
            self::EC_LEVEL_H => [100, 24, [4, 12], [4, 13]],
        ],
        10 => [
            self::EC_LEVEL_L => [274, 18, [2, 68], [2, 69]],
            self::EC_LEVEL_M => [216, 26, [4, 43], [1, 44]],
            self::EC_LEVEL_Q => [154, 24, [6, 19], [2, 20]],
            self::EC_LEVEL_H => [122, 28, [6, 15], [2, 16]],
        ],
        11 => [
            self::EC_LEVEL_L => [324, 20, [4, 81]],
            self::EC_LEVEL_M => [254, 30, [1, 50], [4, 51]],
            self::EC_LEVEL_Q => [180, 28, [4, 22], [4, 23]],
            self::EC_LEVEL_H => [140, 24, [3, 12], [8, 13]],
        ],
        12 => [
            self::EC_LEVEL_L => [370, 24, [2, 92], [2, 93]],
            self::EC_LEVEL_M => [290, 22, [6, 36], [2, 37]],
            self::EC_LEVEL_Q => [206, 26, [4, 20], [6, 21]],
            self::EC_LEVEL_H => [158, 28, [7, 14], [4, 15]],
        ],
        13 => [
            self::EC_LEVEL_L => [428, 26, [4, 107]],
            self::EC_LEVEL_M => [334, 22, [8, 37], [1, 38]],
            self::EC_LEVEL_Q => [244, 24, [8, 20], [4, 21]],
            self::EC_LEVEL_H => [180, 22, [12, 11], [4, 12]],
        ],
        14 => [
            self::EC_LEVEL_L => [461, 30, [3, 115], [1, 116]],
            self::EC_LEVEL_M => [365, 24, [4, 40], [5, 41]],
            self::EC_LEVEL_Q => [261, 20, [11, 16], [5, 17]],
            self::EC_LEVEL_H => [197, 24, [11, 12], [5, 13]],
        ],
    ];

    /**
     * Initialize Galois Field log and antilog tables.
     */
    private static function initGfTables(): void
    {
        if (self::$gfExp !== null) {
            return;
        }

        self::$gfExp = array_fill(0, 512, 0);
        self::$gfLog = array_fill(0, 256, 0);

        $val = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$gfExp[$i] = $val;
            self::$gfLog[$val] = $i;
            $val <<= 1;
            if ($val & 0x100) {
                $val ^= 0x11D; // Primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$gfExp[$i] = self::$gfExp[$i - 255];
        }
    }

    /**
     * Multiply two numbers in GF(256).
     */
    private static function gfMultiply(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        self::initGfTables();

        return self::$gfExp[self::$gfLog[$x] + self::$gfLog[$y]];
    }

    /**
     * Cache of generator polynomials by degree.
     *
     * @var array<int, list<int>>
     */
    private static array $rsGenCache = [];

    /**
     * Compute Reed-Solomon generator polynomial of degree $degree.
     *
     * @return list<int>
     */
    private static function rsGeneratorPoly(int $degree): array
    {
        if (isset(self::$rsGenCache[$degree])) {
            return self::$rsGenCache[$degree];
        }

        self::initGfTables();
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($poly) + 1, 0);
            $factor = self::$gfExp[$i];
            $len = count($poly);
            for ($j = 0; $j < $len; $j++) {
                $next[$j] ^= $poly[$j];
                $next[$j + 1] ^= self::gfMultiply($poly[$j], $factor);
            }
            $poly = $next;
        }

        return self::$rsGenCache[$degree] = $poly;
    }

    /**
     * Calculate Reed-Solomon error correction codewords for a data block.
     *
     * @param  list<int>  $data
     * @return list<int>
     */
    private static function rsEncode(array $data, int $ecCount): array
    {
        $gen = self::rsGeneratorPoly($ecCount);
        $remainder = array_fill(0, $ecCount, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;

            if ($factor !== 0) {
                for ($i = 0; $i < $ecCount; $i++) {
                    $remainder[$i] ^= self::gfMultiply($gen[$i + 1], $factor);
                }
            }
        }

        return $remainder;
    }

    /**
     * Find smallest QR version that can hold the given data length in bytes.
     */
    public static function findSmallestVersion(int $dataLength, int $ecLevel = self::EC_LEVEL_M): int
    {
        foreach (self::$versionSpecs as $v => $ecMap) {
            $spec = $ecMap[$ecLevel];
            $totalDataCodewords = $spec[0];
            // Header overhead: 4 bits mode + (v <= 9 ? 8 : 16) bits char count
            $charCountBits = $v <= 9 ? 8 : 16;
            $headerBytes = (int) ceil((4 + $charCountBits) / 8);
            $maxData = $totalDataCodewords - $headerBytes;

            if ($dataLength <= $maxData) {
                return $v;
            }
        }

        return 14;
    }

    /**
     * Generate 2D QR matrix of booleans (true = dark, false = light).
     *
     * @return array<int, array<int, bool>>
     */
    public static function encodeToMatrix(string $text, int $ecLevel = self::EC_LEVEL_M): array
    {
        $dataBytes = array_values(unpack('C*', $text));
        $dataLen = count($dataBytes);
        $version = self::findSmallestVersion($dataLen, $ecLevel);

        $spec = self::$versionSpecs[$version][$ecLevel];
        $totalDataCodewords = $spec[0];
        $ecPerBlock = $spec[1];

        // 1. Bitstream assembly: Mode (Byte mode = 0100)
        $bits = [false, true, false, false];

        // Character count indicator
        $countBits = $version <= 9 ? 8 : 16;
        for ($i = $countBits - 1; $i >= 0; $i--) {
            $bits[] = (bool) (($dataLen >> $i) & 1);
        }

        // Data bytes
        foreach ($dataBytes as $b) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = (bool) (($b >> $i) & 1);
            }
        }

        // Terminator: up to 4 zero bits
        $capacityBits = $totalDataCodewords * 8;
        $terminatorBits = min(4, $capacityBits - count($bits));
        for ($i = 0; $i < $terminatorBits; $i++) {
            $bits[] = false;
        }

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = false;
        }

        // Pad with alternating 0xEC (236) and 0x11 (17)
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bits) < $capacityBits) {
            $pad = $padBytes[$padIdx % 2];
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = (bool) (($pad >> $i) & 1);
            }
            $padIdx++;
        }

        // Convert bits into data codeword bytes
        $dataCodewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byteVal = 0;
            for ($j = 0; $j < 8; $j++) {
                $byteVal = ($byteVal << 1) | ($bits[$i + $j] ? 1 : 0);
            }
            $dataCodewords[] = $byteVal;
        }

        // 2. Block division and Reed-Solomon EC calculation
        $blocks = [];
        $ecBlocks = [];
        $offset = 0;

        // Group 1
        $g1 = $spec[2];
        for ($b = 0; $b < $g1[0]; $b++) {
            $block = array_slice($dataCodewords, $offset, $g1[1]);
            $blocks[] = $block;
            $ecBlocks[] = self::rsEncode($block, $ecPerBlock);
            $offset += $g1[1];
        }

        // Group 2 (if present)
        if (isset($spec[3])) {
            $g2 = $spec[3];
            for ($b = 0; $b < $g2[0]; $b++) {
                $block = array_slice($dataCodewords, $offset, $g2[1]);
                $blocks[] = $block;
                $ecBlocks[] = self::rsEncode($block, $ecPerBlock);
                $offset += $g2[1];
            }
        }

        // 3. Interleaving data and EC codewords
        $finalCodewords = [];
        $maxBlockData = 0;
        foreach ($blocks as $blk) {
            $maxBlockData = max($maxBlockData, count($blk));
        }

        for ($i = 0; $i < $maxBlockData; $i++) {
            foreach ($blocks as $blk) {
                if ($i < count($blk)) {
                    $finalCodewords[] = $blk[$i];
                }
            }
        }

        for ($i = 0; $i < $ecPerBlock; $i++) {
            foreach ($ecBlocks as $ecBlk) {
                $finalCodewords[] = $ecBlk[$i];
            }
        }

        // Convert final interleaved codewords to stream of bits
        $dataBitStream = [];
        foreach ($finalCodewords as $cw) {
            for ($i = 7; $i >= 0; $i--) {
                $dataBitStream[] = (bool) (($cw >> $i) & 1);
            }
        }

        // Add version remainder bits (e.g. Versions 2-6 have 7 remainder bits)
        $remainderBitsCount = match ($version) {
            2, 3, 4, 5, 6 => 7,
            default => 0,
        };
        for ($i = 0; $i < $remainderBitsCount; $i++) {
            $dataBitStream[] = false;
        }

        // 4. Matrix creation
        $size = 17 + 4 * $version;
        // Matrix: null = unplaced, bool = placed module
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        // Place Finder Patterns & Separators
        self::placeFinderPatterns($matrix, $isFunction, $size);

        // Place Alignment Patterns (Version >= 2)
        if ($version >= 2) {
            self::placeAlignmentPatterns($matrix, $isFunction, $version);
        }

        // Place Timing Patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $color = ($i % 2 === 0);
            if (! $isFunction[6][$i]) {
                $matrix[6][$i] = $color;
                $isFunction[6][$i] = true;
            }
            if (! $isFunction[$i][6]) {
                $matrix[$i][6] = $color;
                $isFunction[$i][6] = true;
            }
        }

        // Dark module at (size - 8, 8)
        $matrix[$size - 8][8] = true;
        $isFunction[$size - 8][8] = true;

        // Reserve Format Information areas
        self::reserveFormatAreas($isFunction, $size);

        // Reserve and place Version Information (Version >= 7)
        if ($version >= 7) {
            self::reserveVersionAreas($isFunction, $size);
            self::writeVersionInfo($matrix, $version, $size);
        }

        // 5. Place Data Bits in 2-column zig-zag traversal
        $bitIdx = 0;
        $bitCount = count($dataBitStream);
        $col = $size - 1;
        $goingUp = true;

        while ($col > 0) {
            if ($col === 6) {
                // Skip vertical timing column
                $col--;
            }

            $rows = $goingUp
                ? range($size - 1, 0, -1)
                : range(0, $size - 1);

            foreach ($rows as $row) {
                for ($c = 0; $c < 2; $c++) {
                    $curCol = $col - $c;
                    if (! $isFunction[$row][$curCol]) {
                        $matrix[$row][$curCol] = $bitIdx < $bitCount ? $dataBitStream[$bitIdx++] : false;
                    }
                }
            }

            $col -= 2;
            $goingUp = ! $goingUp;
        }

        // 6. Mask Evaluation: Choose best mask (0 to 7)
        $bestMask = 0;
        $bestPenalty = PHP_INT_MAX;
        $bestMatrix = $matrix;

        for ($mask = 0; $mask < 8; $mask++) {
            $maskedMatrix = $matrix;
            for ($r = 0; $r < $size; $r++) {
                for ($c = 0; $c < $size; $c++) {
                    if (! $isFunction[$r][$c]) {
                        if (self::getMaskCondition($mask, $r, $c)) {
                            $maskedMatrix[$r][$c] = ! $matrix[$r][$c];
                        }
                    }
                }
            }

            // Write format info for this mask
            self::writeFormatInfo($maskedMatrix, $ecLevel, $mask, $size);

            $penalty = self::calculatePenalty($maskedMatrix, $size);
            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMask = $mask;
                $bestMatrix = $maskedMatrix;
            }
        }

        return $bestMatrix;
    }

    /**
     * Check condition for mask patterns 0 through 7.
     */
    private static function getMaskCondition(int $mask, int $r, int $c): bool
    {
        return match ($mask) {
            0 => (($r + $c) % 2 === 0),
            1 => ($r % 2 === 0),
            2 => ($c % 3 === 0),
            3 => (($r + $c) % 3 === 0),
            4 => (((int) floor($r / 2) + (int) floor($c / 3)) % 2 === 0),
            5 => ((($r * $c) % 2) + (($r * $c) % 3) === 0),
            6 => ((((($r * $c) % 2) + (($r * $c) % 3)) % 2) === 0),
            7 => ((((($r + $c) % 2) + (($r * $c) % 3)) % 2) === 0),
            default => false,
        };
    }

    /**
     * Place standard 7x7 Finder Patterns and 1-module separators.
     *
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private static function placeFinderPatterns(array &$matrix, array &$isFunction, int $size): void
    {
        $corners = [
            [0, 0],
            [0, $size - 7],
            [$size - 7, 0],
        ];

        foreach ($corners as [$top, $left]) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $row = $top + $r;
                    $col = $left + $c;

                    if ($row >= 0 && $row < $size && $col >= 0 && $col < $size) {
                        if ($r >= 0 && $r < 7 && $c >= 0 && $c < 7) {
                            $isDark = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                            $matrix[$row][$col] = $isDark;
                        } else {
                            $matrix[$row][$col] = false; // Separator
                        }
                        $isFunction[$row][$col] = true;
                    }
                }
            }
        }
    }

    /**
     * Place 5x5 Alignment Patterns for Version >= 2.
     *
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private static function placeAlignmentPatterns(array &$matrix, array &$isFunction, int $version): void
    {
        $coords = self::$alignmentCoordinates[$version] ?? [];
        $count = count($coords);

        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $count; $j++) {
                $centerR = $coords[$i];
                $centerC = $coords[$j];

                // Skip positions that overlap with Finder Patterns
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $count - 1) || ($i === $count - 1 && $j === 0)) {
                    continue;
                }

                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        $isDark = ($r === -2 || $r === 2 || $c === -2 || $c === 2 || ($r === 0 && $c === 0));
                        $matrix[$centerR + $r][$centerC + $c] = $isDark;
                        $isFunction[$centerR + $r][$centerC + $c] = true;
                    }
                }
            }
        }
    }

    /**
     * Reserve Format Information areas.
     *
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private static function reserveFormatAreas(array &$isFunction, int $size): void
    {
        // Top-left format areas (Row 8, cols 0..8 and Col 8, rows 0..8)
        for ($i = 0; $i <= 8; $i++) {
            $isFunction[8][$i] = true;
            $isFunction[$i][8] = true;
        }

        // Top-right format area (Row 8, cols size-8..size-1)
        // Bottom-left format area (Col 8, rows size-8..size-1, including dark module at size-8)
        for ($i = 0; $i < 8; $i++) {
            $isFunction[8][$size - 1 - $i] = true;
            $isFunction[$size - 1 - $i][8] = true;
        }
    }

    /**
     * Standard 18-bit BCH (18, 6) Version Information codes for QR versions 7 to 14.
     *
     * @var array<int, int>
     */
    private static array $versionInfoCodes = [
        7 => 0x07C94,
        8 => 0x085BC,
        9 => 0x09A99,
        10 => 0x0A4D3,
        11 => 0x0BBF6,
        12 => 0x0C762,
        13 => 0x0D847,
        14 => 0x0E60D,
    ];

    /**
     * Reserve 18-bit Version Information areas for Version >= 7.
     *
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private static function reserveVersionAreas(array &$isFunction, int $size): void
    {
        for ($i = 0; $i < 18; $i++) {
            $r = (int) floor($i / 3);
            $c = $size - 11 + ($i % 3);
            $isFunction[$r][$c] = true;
            $isFunction[$c][$r] = true;
        }
    }

    /**
     * Write 18 bits of Version Information for Version >= 7.
     *
     * @param  array<int, array<int, bool|null>>  $matrix
     */
    private static function writeVersionInfo(array &$matrix, int $version, int $size): void
    {
        $vBits = self::$versionInfoCodes[$version] ?? 0;
        if ($vBits === 0) {
            return;
        }

        for ($i = 0; $i < 18; $i++) {
            $bit = (bool) (($vBits >> $i) & 1);
            $r = (int) floor($i / 3);
            $c = $size - 11 + ($i % 3);
            $matrix[$r][$c] = $bit;
            $matrix[$c][$r] = $bit;
        }
    }

    /**
     * Write 15 bits of Format Information (Error Correction Level + Mask) with BCH (15,5).
     *
     * @param  array<int, array<int, bool>>  $matrix
     */
    private static function writeFormatInfo(array &$matrix, int $ecLevel, int $mask, int $size): void
    {
        $data = ($ecLevel << 3) | $mask;
        $rem = $data << 10;
        $generator = 0x537; // 10100110111 in binary

        for ($i = 4; $i >= 0; $i--) {
            if (($rem >> ($i + 10)) & 1) {
                $rem ^= ($generator << $i);
            }
        }

        $formatBits = (($data << 10) | $rem) ^ 0x5412; // XOR mask 101010000010010

        // Write 15 bits of Format Information according to ISO/IEC 18004
        for ($i = 0; $i < 15; $i++) {
            $bit = (bool) (($formatBits >> $i) & 1);

            // Vertical layout (Column 8)
            if ($i < 6) {
                $matrix[$i][8] = $bit;
            } elseif ($i < 8) {
                $matrix[$i + 1][8] = $bit;
            } else {
                $matrix[$size - 15 + $i][8] = $bit;
            }

            // Horizontal layout (Row 8)
            if ($i < 8) {
                $matrix[8][$size - $i - 1] = $bit;
            } elseif ($i === 8) {
                $matrix[8][7] = $bit;
            } else {
                $matrix[8][14 - $i] = $bit;
            }
        }
    }

    /**
     * Calculate penalty points for the masked QR matrix (ISO 18004 N1, N2, N3, N4 rules).
     *
     * @param  array<int, array<int, bool>>  $m
     */
    private static function calculatePenalty(array $m, int $size): int
    {
        $penalty = 0;

        // Rule N1: 5 or more consecutive same-color modules in row/column
        for ($r = 0; $r < $size; $r++) {
            $runLen = 1;
            for ($c = 1; $c < $size; $c++) {
                if ($m[$r][$c] === $m[$r][$c - 1]) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) {
                        $penalty += 3 + ($runLen - 5);
                    }
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) {
                $penalty += 3 + ($runLen - 5);
            }
        }

        for ($c = 0; $c < $size; $c++) {
            $runLen = 1;
            for ($r = 1; $r < $size; $r++) {
                if ($m[$r][$c] === $m[$r - 1][$c]) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) {
                        $penalty += 3 + ($runLen - 5);
                    }
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) {
                $penalty += 3 + ($runLen - 5);
            }
        }

        // Rule N2: 2x2 blocks of same color
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $color = $m[$r][$c];
                if ($m[$r + 1][$c] === $color && $m[$r][$c + 1] === $color && $m[$r + 1][$c + 1] === $color) {
                    $penalty += 3;
                }
            }
        }

        // Rule N4: Proportion of dark modules
        $darkCount = 0;
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($m[$r][$c]) {
                    $darkCount++;
                }
            }
        }
        $totalModules = $size * $size;
        $percent = ($darkCount * 100) / $totalModules;
        $prevMultOf5 = (int) (floor($percent / 5) * 5);
        $nextMultOf5 = (int) (ceil($percent / 5) * 5);
        $diff = min(abs($prevMultOf5 - 50), abs($nextMultOf5 - 50)) / 5;
        $penalty += (int) ($diff * 10);

        return $penalty;
    }

    /**
     * Render QR code as SVG string.
     *
     * @param  string  $text  The string content to encode (e.g. UPI URI)
     * @param  int  $size  Output display size in px (default 300)
     * @param  int  $margin  Quiet zone margin in modules (default 4)
     * @param  string  $fgColor  Foreground hex color (default #000000)
     * @param  string  $bgColor  Background hex color (default #ffffff)
     */
    public function renderSvg(
        string $text,
        int $size = 300,
        int $margin = 4,
        string $fgColor = '#000000',
        string $bgColor = '#ffffff',
        int $ecLevel = self::EC_LEVEL_M
    ): string {
        $matrix = self::encodeToMatrix($text, $ecLevel);
        $matrixSize = count($matrix);
        $totalSize = $matrixSize + ($margin * 2);

        // Build combined SVG path for performance & crisp vector sharpness
        $pathData = '';
        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $pathData .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        $cleanPath = trim($pathData);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 {$totalSize} {$totalSize}" width="{$size}" height="{$size}" role="img" aria-label="QR Code">
    <rect width="100%" height="100%" fill="{$bgColor}" />
    <path d="{$cleanPath}" fill="{$fgColor}" shape-rendering="crispEdges" />
</svg>
SVG;
    }

    /**
     * Render QR code as Data URI (base64 SVG).
     */
    public function renderDataUri(
        string $text,
        int $size = 300,
        int $margin = 4,
        string $fgColor = '#000000',
        string $bgColor = '#ffffff',
        int $ecLevel = self::EC_LEVEL_M
    ): string {
        $svg = $this->renderSvg($text, $size, $margin, $fgColor, $bgColor, $ecLevel);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
