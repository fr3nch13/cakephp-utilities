<?php
declare(strict_types=1);

/**
 * ColorHelper
 */

namespace Fr3nch13\Utilities\View\Helper;

use Cake\View\Helper;
use Fr3nch13\Utilities\Lib\Hlls;

/**
 * Color Helper
 *
 * Used to conver colors to different formats, and determine things like hue, lightness, etc.
 */
class ColorHelper extends Helper
{
    /**
     * Converts hex colors to rgb.
     *
     * @param string $hexCode The hex code of the color. This detects the '#' char.
     * @return int The RGB code.
     */
    public function hexToRgb(string $hexCode)
    {
        if ($hexCode[0] == '#') {
            $hexCode = substr($hexCode, 1);
        }
        if (!$hexCode[4]) { // WTF phpstan for php7.4???
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }
        $r = hexdec($hexCode[0] . $hexCode[1]);
        $g = hexdec($hexCode[2] . $hexCode[3]);
        $b = hexdec($hexCode[4] . $hexCode[5]);

        return intval($b + ($g << 0x8) + ($r << 0x10));
    }

    /**
     * Gets the hue, lightness, luminosity, and saturation of the color.
     *
     * @param int $RGB The RGB code.
     * @return \Fr3nch13\Utilities\Lib\Hlls The object containing the info needed.
     */
    public function rgbToHlls(int $RGB)
    {
        $r = 0xff & $RGB >> 0x10;
        $g = 0xff & $RGB >> 0x8;
        $b = 0xff & $RGB;
        $r = (float)$r / 255.0;
        $g = (float)$g / 255.0;
        $b = (float)$b / 255.0;
        $li = (int)round((0.2126 * $r + 0.7151999999999999 * $g + 0.0722 * $b) * 100);
        $maxC = max($r, $g, $b);
        $minC = min($r, $g, $b);
        $l = ($maxC + $minC) / 2.0;
        if ($maxC == $minC) {
            $s = 0;
            $h = 0;
        } else {
            $h = 0;
            if ($l < 0.5) {
                $s = ($maxC - $minC) / ($maxC + $minC);
            } else {
                $s = ($maxC - $minC) / (2.0 - $maxC - $minC);
            }
            if ($r == $maxC) {
                $h = ($g - $b) / ($maxC - $minC);
            }
            if ($g == $maxC) {
                $h = 2.0 + ($b - $r) / ($maxC - $minC);
            }
            if ($b == $maxC) {
                $h = 4.0 + ($r - $g) / ($maxC - $minC);
            }
            $h = $h / 6.0;
        }
        $h = (int)round(255.0 * $h);
        $s = (int)round(255.0 * $s);
        $l = (int)round(255.0 * $l);

        return new Hlls($h, $li, $l, $s);
    }
}
